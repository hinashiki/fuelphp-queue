<?php
/**
 * Queue Package
 *
 * @package    Queue
 * @version    0.1
 * @author     Hinashiki
 * @license    MIT License
 * @copyright  2015 - Hinashiki
 * @link       https://github.com/hinashiki/fuelphp-queue
 */
namespace Fuel\Tasks;
class Queues extends \TaskBase
{
	private $__process_file_name = '';

	/**
	 * manage and execute queues
	 *
	 */
	public function run()
	{
		// check pids
		if(\Config::get('queue.queue_parallel_number') <= count(glob(\APPPATH.'tmp/'.\Config::get('queue.queue_pid_prefix').'_*.pid')))
		{
			\Log::debug('queue limit over.');
			return;
		}
		$this->__process_file_name = \Config::get('queue.queue_pid_prefix').'_'.getmypid().'.pid';
		// create pid file
		\File::create(\APPPATH.'tmp', $this->__process_file_name);

		try
		{
			// check dead queues
			$recover = \Model_TaskQueue::recover_zombie_queues();
			if( ! empty($recover))
			{
				$callback = \Config::get('queue.task_notify_callback');
				$callback('zombie task recovered. ids = '.implode(',', $recover));
			}
			// check queues
			$queue = \Model_TaskQueue::pickup();
			if(empty($queue))
			{
				return;
			}

			// exec queue
			call_user_func_array(
				'\\'.$queue['method'],
				\Format::forge($queue['options'], 'json')->to_array()
			);

			// finish (update to success) queue
			\Model_TaskQueue::finish($queue['id'], \Model_TaskQueue::STATUS_SUCCESS);
		}
		catch(\Exception $e)
		{
			if(\DB::in_transaction())
			{
				\DB::rollback_transaction();
			}
			if( isset($queue) and ! empty($queue))
			{
				// finish (update to error) queue
				\Model_TaskQueue::finish($queue['id'], \Model_TaskQueue::STATUS_ERROR);
				// send to callback method
				$body = array();
				$body[] = 'oisyna task queue error occured.';
				$body[] = '--------------------------------';
				$body[] = 'id: '.$queue['id'];
				$body[] = 'method: '.$queue['method'];
				$body[] = 'options: '.$queue['options'];
				if(is_array($e->getMessage()))
				{
					$body[] = 'reason: '.implode(' ', $e->getMessage());
				}
				elseif( ! empty($e->getMessage()))
				{
					$body[] = 'reason: '.$e->getMessage();
				}
				$callback = \Config::get('queue.task_notify_callback');
				$callback($body);
			}
		}
		finally
		{
			\File::delete(\APPPATH.'tmp/'.$this->__process_file_name);
		}
	}

	/**
	 * clean queues
	 *
	 */
	public function clean()
	{
		try
		{
			\DB::start_transaction();
			$query = \DB::delete('task_queues')->where('job_status', \Model_TaskQueue::STATUS_SUCCESS)
				->where('updated_at', '<=', date('Y-m-d', strtotime(\Config::get('queue.success_queue_delete_term'))));
			$query->execute();
			\DB::commit_transaction();
		}
		catch(\Exception $e)
		{
			\DB::rollback_transaction();
		}
	}
}
