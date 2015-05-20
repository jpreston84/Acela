<?php
/**
 * A singleton static instance test routine.
 */

require_once __DIR__.'/../Core/Core.php';

abstract class foo
{
	public function go()
	{
		static $x = 0;
		$x++;
		echo $x.'<br />';
	}
}

class a extends foo
{
}

class b extends foo
{
}

$a = new a();
$b = new b();

$a->go();
$a->go();
$a->go();
$b->go();
$b->go();
$b->go();

