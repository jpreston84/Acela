<?php
namespace Acela\Core\Config;

/**
 *  A list of all the database connections used by this application.
 *  
 *  @var $databases
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