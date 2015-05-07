<?php
/**
 * A test routine for Models.
 */

namespace Acela\Application;

use \Acela\Core as Core;

require_once __DIR__.'/../Core/Core.php';

$userManager = Core\Model::getInstance('User');

$user = $userManager->create();
$user->firstName = 'Jonathan';
$user->lastName = 'Preston';
$user->save();

print_r($user);

$users = $userManager->get( [ 'firstName' => 'Jonathan' ], 1 );
print_r($users);
