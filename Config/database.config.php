<?php
/**
 *  Database configuration.
 */

namespace Acela\Core\Database;

/**
 *  A list of all the database connections used by this application.
 */
$GLOBALS['core']->config->database = new \stdClass();

/**
 * The default MySQL database configuration.
 */
$GLOBALS['core']->config->database->default			= new Drivers\MySQL\Configuration;
$GLOBALS['core']->config->database->default->name		= 'default';
$GLOBALS['core']->config->database->default->host		= 'localhost';
$GLOBALS['core']->config->database->default->username	= 'acela';
$GLOBALS['core']->config->database->default->password	= 'password';
$GLOBALS['core']->config->database->default->database	= 'acela';
