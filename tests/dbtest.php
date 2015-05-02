<?php
require_once __DIR__.'/../core/core.php';

$query = $core->db->query();

$query
	->table('users', 'u')
;

echo '<pre>'.$query->build().'</pre>';

$results = $query->run();

foreach($results as $result)
{
	echo '<br />';
	print_r($result);
}