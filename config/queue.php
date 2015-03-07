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
return array(

	/**
	 * duplicate_type - setting of each type limit
	 * you can set int key and int value.
	 * key = duplicate_type value in db.
	 * value = parallel exec limit.
	 * if not set value in this config and set value in db value,
	 * Model_TaskQueue::pickup() throw OutOfRangeException.
	 *
	 * can't set 0 (default) key in this array.
	 */
	'duplicate_type' => array(
		// ex.
		// 1 => 5,
		// 2 => 1,
		// 3 => 3,
		// 4 => 5,
	),

	/**
	 * zombie_recover_time - limitation of queue exec time.
	 * if queue status stay 'exec' over this time,
	 * process's status is back to 'wait'
	 * when use Model_Taskqueue::recover_zombie_queues().
	 *
	 * @see
	 * Model_Taskqueue::recover_zombie_queues()
	 */
	'zombie_recover_time' => '-1 hour',

	/**
	 * success_queue_delete_term
	 * term of queue record's physical delete.
	 *
	 * @see
	 * Fuel\Tasks\Queues::clean()
	 */
	'success_queue_delete_term' => '-14 days',

	/**
	 * queue_parallel_count
	 * max num of queue you can exec in parallel
	 * this number apply in each server.
	 */
	'queue_parallel_number' => 3,

	/**
	 * queue_pid_prefix
	 */
	'queue_pid_prefix' => 'queue',

	/**
	 * logical_delete_option
	 *
	 */
	'logical_delete' => array(
		'not_deleted' => 0,
		'deleted' => 1,
	),

	/**
	 * task_notify_callback
	 *
	 * @param string $message
	 */
	'task_notify_callback' => function($message)
	{
		\Log::warning($message);
	},
);
