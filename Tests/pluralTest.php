<?php
/**
 * A plural test routine.
 */

require_once __DIR__.'/../Core/Core.php';

echo 'Pluralize<br />';
echo wordPluralize('potato').'<br />';
echo wordPluralize('fish').'<br />';
echo wordPluralize('ox').'<br />';
echo wordPluralize('quiz').'<br />';
echo wordPluralize('gas').'<br />';
echo wordPluralize('cow').'<br />';

echo 'Singularize<br />';
echo wordPluralize('potatoes').'<br />';
echo wordPluralize('fish').'<br />';
echo wordPluralize('oxen').'<br />';
echo wordPluralize('quizzes').'<br />';
echo wordPluralize('gases').'<br />';
echo wordPluralize('cows').'<br />';
