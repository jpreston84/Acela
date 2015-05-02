<?php
/**
 *  Database configuration.
 */

namespace Acela\Core\Database;

/**
 *  A list of all the database connections used by this application.
 */
$GLOBALS['core']->config->databases = new \stdClass();

/**
 * The default MySQL database configuration.
 */
$GLOBALS['core']->config->databases->default			= new Drivers\MySQL\Configuration;
$GLOBALS['core']->config->databases->default->name		= 'default';
$GLOBALS['core']->config->databases->default->host		= 'localhost';
$GLOBALS['core']->config->databases->default->username	= 'acela';
$GLOBALS['core']->config->databases->default->password	= 'password';
$GLOBALS['core']->config->databases->default->database	= 'acela';
