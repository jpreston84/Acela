<?php
/**
 * A test routine for Models.
 */

namespace Acela\Application;

use \Acela\Core as Core;

require_once __DIR__.'/../Core/Core.php';

$userManager = Core\Model::getInstance('User');
$users = $userManager->get(
	[
		'firstName' => 'Bob',
		['firstName', 'Larry'],
		['lastName', 'LIKE' '%Joe%'],
		['id', '>' 10],
	],
	10
);
print_r($users);
foreach($users as $user)
{
	print_r($user);
}


/* 
$userManager = Core\Model::get('User');
$userManager->name = 'Instance 1';

print_r($userManager);

$userManager = new Core\Models\User\Manager;
$userManager->name = 'Instance 2';

print_r($userManager);

$userManager = Core\Model::get('User');

print_r($userManager);
 */