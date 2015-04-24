<?php
/**
 *  Database configuration.
 */

namespace Acela\Core\Config;

/**
 *  A list of all the database connections used by this application.
 *  
 *  @global array $databases
 */
$databases = [
	'default' => [
		'driver'	=> 'MySQL',
		'host'		=> 'localhost',
		'username'	=> 'mysql_user',
		'password'	=> 'mysql_password',
		'database'	=> 'acela',
		'readOnly'	=> false,
	],
];