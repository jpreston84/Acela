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

class aa extends a
{
}

$a = new a();
$b = new b();
$aa = new aa();
$c = new a();

$a->go();
$a->go();
$a->go();
$b->go();
$b->go();
$b->go();
$aa->go();
$aa->go();
$aa->go();
$c->go();
$c->go();
$c->go();

