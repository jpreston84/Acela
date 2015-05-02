<?php
/**
 * Acela - A PHP Rapid Development Framework
 * 
 * Main file.
 */

namespace Acela\Core;

/**
 * Core object that contains references to class instances and holds
 * configuration data.
 *  
 * @global \stdClass $GLOBALS['core']
 * @name $core
 */
$GLOBALS['core'] = new \stdClass;

/**
 * Load the autoloader functions.
 */
require_once __DIR__.'/functions/autoload.functions.php';
spl_autoload_register('Acela\Core\autoloadClasses');

/**
 * Include the global configuration file.
 */
require_once __DIR__.'/../config/config.php';

/**
 * Load the database engine.
 */
require_once __DIR__.'/classes/database/engine.class.php';
require_once __DIR__.'/../config/database.config.php';
$core->db = new Database\Engine($core->config->databases->default);
