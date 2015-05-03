<?php
/**
 * A database test routine.
 */

require_once __DIR__.'/../Core/Core.php';

$query = $core->db->query();

$query
	->table('users', 'u')
;

echo '<pre>'.$query->build().'</pre>';

$results = $query->run();

print_r($results);

foreach($results as $result)
{
	echo '<br />Going through results';
	print_r($result);
}
