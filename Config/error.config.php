<?php
/**
 *  Error configuration.
 */

namespace Acela\Core;

$GLOBALS['core']->config->error = new \stdClass();

/**
 * The default error log configuration.
 */
$GLOBALS['core']->config->error->default			= new \stdClass();
$GLOBALS['core']->config->error->default->name		= 'default';
$GLOBALS['core']->config->error->default->path		= __DIR__.'/../Logs/general.log';
