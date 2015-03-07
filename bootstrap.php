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
\Package::load('orm');
\Autoloader::add_namespace('Queue', __DIR__.'/classes/');
\Autoloader::add_core_namespace('Queue');
\Autoloader::add_classes(array(
	// 'Seo\\Seo'        => __DIR__.'/classes/seo.php',
));
\Config::load('queue', true);
