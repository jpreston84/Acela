<?php
/**
 * A test for the new Schema object.
 */

namespace Acela\Application;

use \Acela\Core;
 
require_once __DIR__.'/../Core/Core.php';

echo '<h1>Schema Test</h1>';

$schema = Core\Database\Engine::schema();
$schema->deleteTable('what2');
$schema->save();

$schema->get('what')->copy('what2');

echo '<br /><b>Done.</b>';