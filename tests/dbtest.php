<?php
require_once __DIR__.'/../core/core.php';

$query = $core->db->query();

$query
	->table('users', 'u')
	->table('companies', 'c')
		->cond('companyId')
	->table('compGroups', 'cg')
		->cond(['cg', 'groupId'], '=', ['c', 'groupId'])
	->where('u', 'userId', '=', 123)
	->a()->group()
		->where('c', 'companyName', 'LIKE', '%foo%')
		->o()->where('c', 'companyName', 'LIKE', '%bar%')
		->a()->where('c', 'companyName', 'LIKE', '%baz%')
	->groupEnd()
	->o()->group()
		->where('cg', 'boolField', '=', null)
		->o()->where('cg', 'boolField', '=', true)
		->o()->where('cg', 'boolField', '=', false)
		->o()->group()
			->where('u', 'randomField', '=', 'string')
			->a()->where('u', 'randomField', '=', 999)
		->groupEnd()
	->groupEnd();

echo '<pre>$query->buildQuery()</pre>';