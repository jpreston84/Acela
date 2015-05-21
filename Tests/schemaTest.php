<?php
/**
 * A test for the new Schema object.
 */

namespace Acela\Application;

use \Acela\Core;
 
require_once __DIR__.'/../Core/Core.php';

$table = Core\Schema::get('users');

echo '<pre>';
foreach($table as $field)
{
	print_r($table);
	echo '<br />';
}
echo '</pre>';
