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
\Package::load('orm');
\Autoloader::add_namespace('Queue', __DIR__.'/classes/');
\Autoloader::add_core_namespace('Queue');
\Autoloader::add_classes(array(
	'Queue\\Model_TaskQueue' => __DIR__.'/classes/model/taskqueue.php',
));
\Config::load('queue', true);
