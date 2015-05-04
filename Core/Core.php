<?php
/**
 * Acela - A PHP Rapid Development Framework
 * 
 * Main file.
 */

namespace Acela\Core;

/**
 * Load the autoloader functions.
 */
require_once __DIR__.'/Functions/Autoload.functions.php';
spl_autoload_register('Acela\Core\autoloadClasses');

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
 *  
 * @global Core $GLOBALS['core']
 */
$GLOBALS['core'] = new Core;

/**
 * Load the database engine.
 */
$core->db = new Database\Engine($core->config->database->default);
