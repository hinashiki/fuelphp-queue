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
namespace Fuel\Migrations;
class Queue_create_task_queues_table
{
	public function up()
	{
		if(\DBUtil::table_exists('task_queues'))
		{
			return;
		}

		// -------------------------
		// task_queues
		// -------------------------
		\DBUtil::create_table(
			'task_queues',
			array(
				'id' => array(
					'type' => 'int',
					'constraint' => 10,
					'unsigned' => true,
					'auto_increment' => true,
				),
				'method' => array(
					'type' => 'varchar',
					'constraint' => 255,
				),
				'options' => array(
					'type' => 'varchar',
					'constraint' => 255,
					'comment' => 'json format',
				),
				'duplicate_type' => array(
					'type' => 'tinyint',
					'default' => 0,
					'comment' => '0:no limit setting, 1~:limit is refered from config file'
				),
				'job_status' => array(
					'type' => 'tinyint',
					'default' => 0,
					'comment' => '0:wait, 1:exec, 2:success, 3:error',
				),
				'deleted' => array(
					'type' => 'tinyint',
					'default' => 0,
				),
				'created_at' => array(
					'type' => 'datetime',
					'null' => true,
				),
				'updated_at' => array(
					'type' => 'datetime',
					'null' => true,
				),
				'timestamp' => array(
					'type' => 'timestamp',
					'default' => \DB::expr('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
				),
			),
			array('id'),
			true,
			'InnoDB',
			'utf8'
		);
	}

	public function down()
	{
		// drop
		\DBUtil::drop_table('task_queues');
	}
}
