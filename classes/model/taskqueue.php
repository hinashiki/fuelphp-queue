<?php
/**
 * Queue Package
 *
 * @package    Queue
 * @version    0.2
 * @author     Hinashiki
 * @license    MIT License
 * @copyright  2015 - Hinashiki
 * @link       https://github.com/hinashiki/fuelphp-queue
 */
namespace Queue;
class Model_TaskQueue extends \Orm\Model
{

	const STATUS_WAIT    = 0;
	const STATUS_EXEC    = 1;
	const STATUS_SUCCESS = 2;
	const STATUS_ERROR   = 3;
	const DUPLICATE_TYPE_NONE = 0;

	protected static $_properties = array(
		'id',
		'method',
		'options',
		'priority',
		'duplicate_type',
		'job_status',
		'deleted',
		'created_at',
		'updated_at',
	);

	protected static $_observers = array(
		'\\Orm\\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => true,
			'property' => 'created_at',
		),
		'\\Orm\\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => true,
			'property' => 'updated_at',
		),
	);

	protected static $_table_name = 'task_queues';

	/**
	 * キューの取得、実行中への更新
	 *
	 * @param array $exclude_type 除外するduplicate_type
	 * @return array queue info
	 * @throw OutOfRangeException
	 */
	public static function pickup($exclude_type = array())
	{
		\DB::start_transaction();
		$query = \DB::select('*')
			->from('task_queues')
			->where('job_status', static::STATUS_WAIT)
			->where('deleted', \Config::get('queue.logical_delete.not_deleted'))
			->limit(1)
			->order_by('priority', 'ASC')
			->order_by('id', 'ASC');
		if( ! empty($exclude_type))
		{
			$query->where('duplicate_type', 'NOT IN', $exclude_type);
		}
		$compiled = $query->compile();
		$query = \DB::query($compiled.' FOR UPDATE');
		$result = $query->execute()->as_array();
		if(empty($result))
		{
			\DB::rollback_transaction();
			return array();
		}
		// control limit
		if($result[0]['duplicate_type'] != static::DUPLICATE_TYPE_NONE)
		{
			$task_queue_limit = \Config::get('queue.duplicate_type');
			if( ! isset($task_queue_limit[intval($result[0]['duplicate_type'])]))
			{
				throw new \OutOfRangeException('taks_queues.duplicate_type: '.$result[0]['duplicate_type'].' is not defined.');
			}
			$limit = $task_queue_limit[intval($result[0]['duplicate_type'])];
			$count = \DB::select(\DB::expr('COUNT(*) as cnt'))
				->from('task_queues')
				->where('job_status', static::STATUS_EXEC)
				->where('duplicate_type', $result[0]['duplicate_type'])
				->where('deleted', \Config::get('queue.logical_delete.not_deleted'))
				->execute()->as_array();
			if($count[0]['cnt'] >= $limit)
			{
				\DB::rollback_transaction();
				// add exclude_type and retry pickup
				$exclude_type[] = $result[0]['duplicate_type'];
				return self::pickup($exclude_type);
			}
		}
		// update job_status
		$TaskQueue = static::find($result[0]['id']);
		$TaskQueue->job_status = static::STATUS_EXEC;
		$TaskQueue->save();
		\DB::commit_transaction();
		return $result[0];
	}

	/**
	 * キューの終了
	 *
	 * @param int $id
	 *        int $job_status
	 * @return void
	 */
	public static function finish($id, $job_status)
	{
		$TaskQueue = static::find($id);
		$TaskQueue->job_status = $job_status;
		$TaskQueue->save();
		return;
	}

	/**
	 * キューの保存
	 *
	 * @param string $method
	 *        array  $options
	 *        int    $duplicate_type
	 *        int    $priority
	 * @return void
	 */
	public static function save_queue($method, $options, $duplicate_type = null, $priority = null)
	{
		if(is_null($duplicate_type))
		{
			$duplicate_type = static::DUPLICATE_TYPE_NONE;
		}
		if(is_null($priority))
		{
			$priority = \Config::get('queue.queue_default_priority');
		}
		$save_data = array(
			'method'         => $method,
			'options'        => \Format::forge($options)->to_json(),
			'priority'       => $priority,
			'duplicate_type' => $duplicate_type,
			'job_status'     => static::STATUS_WAIT,
			'deleted'        => \Config::get('queue.logical_delete.not_deleted'),
		);
		self::forge($save_data)->save();
	}

	/**
	 * ゾンビタスクの救済
	 *
	 * @return array task_ids
	 */
	public static function recover_zombie_queues()
	{
		$return = array();
		// check zombie
		$tasks = \DB::select('id')->from(self::$_table_name)
			->where('job_status', static::STATUS_EXEC)
			->where('deleted', \Config::get('queue.logical_delete.not_deleted'))
			->where('updated_at', '<=', date('Y-m-d H:i:s', strtotime(\Config::get('queue.zombie_recover_time'))))
			->execute()->as_array();
		foreach($tasks as $task)
		{
			$return[] = intval($task['id']);
			// recover
			$TaskQueue = static::find($task['id']);
			$TaskQueue->job_status = static::STATUS_WAIT;
			$TaskQueue->save();
		}
		return $return;
	}

	/**
	 * notify function
	 *
	 * @param mixed $msg
	 * @return void
	 */
	public static function notify($msg)
	{
		if(is_array($msg))
		{
			$msg = implode("\n", $msg);
		}
		\Log::warning($msg);
	}

}
