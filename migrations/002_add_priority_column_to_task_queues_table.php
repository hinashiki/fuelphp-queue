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
namespace Fuel\Migrations;
class Add_priority_column_to_task_queues_table
{
	public function up()
	{
		\DBUtil::add_fields('task_queues', array(
			'priority' => array(
				'type' => 'tinyint',
				'default' => 0,
				'unsigned' => true,
				'comment' => 'task\'s priority. 0 has top priority. you can setting default priority by config.'
			),
		));
	}

	public function down()
	{
		// drop
		\DBUtil::drop_fields('task_queues', 'priority');
	}
}
