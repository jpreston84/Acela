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
$user->lastName = 1;

for($i = 1; $i <= 10; $i++)
{
	$user = $userManager->getFirst( [ 'firstName' => 'Jonathan' ] );
	$user->lastName++;
	$user->save();
}
