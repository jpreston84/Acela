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
echo Core\wordSingularize('potatoes').'<br />';
echo Core\wordSingularize('fish').'<br />';
echo Core\wordSingularize('oxen').'<br />';
echo Core\wordSingularize('quizzes').'<br />';
echo Core\wordSingularize('gases').'<br />';
echo Core\wordSingularize('cows').'<br />';
