<?php
/**
 * A plural test routine.
 */

namespace Acela\Application;

use \Acela\Core as Core;

require_once __DIR__.'/../Core/Core.php';

echo 'Pluralize<br />';
echo Core\wordPluralize('potato').'<br />';
echo Core\wordPluralize('fish').'<br />';
echo Core\wordPluralize('ox').'<br />';
echo Core\wordPluralize('quiz').'<br />';
echo Core\wordPluralize('gas').'<br />';
echo Core\wordPluralize('cow').'<br />';

echo 'Singularize<br />';
echo Core\wordPluralize('potatoes').'<br />';
echo Core\wordPluralize('fish').'<br />';
echo Core\wordPluralize('oxen').'<br />';
echo Core\wordPluralize('quizzes').'<br />';
echo Core\wordPluralize('gases').'<br />';
echo Core\wordPluralize('cows').'<br />';
