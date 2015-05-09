<?php
/**
 * A test routine for Models.
 */

namespace Acela\Application;

use \Acela\Core as Core;

require_once __DIR__.'/../Core/Core.php';

$userManager = Core\Model::getInstance('User');

$user = $userManager->getFirst( [] );

$user->firstName .= ' - Modified';
$user->save();
