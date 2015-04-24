<?php
/**
 *  Database configuration.
 */

/**
 *  A list of all the database connections used by this application.
 */
$GLOBALS['core']->config->database = new \stdClass();
$GLOBALS['core']->config->database->connections = [
	'default' => [
		'driver'	=> 'MySQL',
		'host'		=> 'localhost',
		'username'	=> 'mysql_user',
		'password'	=> 'mysql_password',
		'database'	=> 'acela',
		'readOnly'	=> false,
	],
];
