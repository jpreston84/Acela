<?php
/**
 * Acela - A PHP Rapid Development Framework
 * 
 * Main file.
 */

/**
 * Core object that contains references to class instances and holds
 * configuration data.
 *  
 * @global \stdClass $GLOBALS['core']
 * @name $core
 */
$GLOBALS['core'] = new \stdClass;

/**
 * Include the global configuration file.
 */
require_once __DIR__.'/../config/config.php';
