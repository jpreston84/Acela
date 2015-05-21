<?php
/**
 * A test for the new Schema object.
 */

 
require_once __DIR__.'/../Core/Core.php';

$table = Schema::get('users');

echo '<pre>';
foreach($table as $field)
{
	print_r($table);
	echo '<br />';
}
echo '</pre>';
