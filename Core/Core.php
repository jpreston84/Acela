<?php
/**
 * Acela - A PHP Rapid Development Framework
 * 
 * Main file.
 */

namespace Acela\Core;

/**
 * Load Acela autoloader.
 */
require_once __DIR__.'/Functions/Autoload.functions.php';
spl_autoload_register('Acela\Core\autoloadClasses');

/**
 * Load Composer autoloader.
 */
require_once __DIR__.'/../Vendor/autoload.php';

/**
 * Load all remaining core function files.
 */
foreach(glob(__DIR__.'/Functions/*.functions.php') as $filename)
{
	require_once $filename;
}

/**
 * Core object that contains references to class instances and holds
 * configuration data.
 */
$GLOBALS['core'] = new Core;

/**
 * Set default error handler.
 */
// set_error_handler('\Acela\Core\Error::phpError');

/**
 * Load the database engine.
 */
$core->db = new Database\Engine($core->config->database->default);
