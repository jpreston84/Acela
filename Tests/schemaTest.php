<?php
/**
 * A test for the new Schema object.
 */

namespace Acela\Application;

use \Acela\Core;
 
require_once __DIR__.'/../Core/Core.php';

echo '<h1>Schema Test</h1>';

// $table = Core\Database\Engine::schema()->get('users');
$table = Core\Database\Engine::schema();

echo '<pre>';
foreach($table as $field)
{
	print_r($table);
	echo '<br />';
}
echo '</pre>';
