<?php
/**
 * A test routine for Models.
 */

namespace Acela\Application;

use \Acela\Core as Core;

require_once __DIR__.'/../Core/Core.php';

$fooManager = Core\Model::getInstance('Foo');

$foo = $fooManager->getFirst();

echo 'Foo';
echo '<pre>';
print_r($foo);
echo '</pre>';

$bar = $foo->getFirstBar();

echo 'Bar';
echo '<pre>';
print_r($bar);
echo '</pre>';
