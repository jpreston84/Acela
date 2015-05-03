<?php
/**
 * The core object.
 */

namespace Acela\Core;

/**
 * The core object.
 */
class Core
{
	/**
	 * @var Config $config Global configuration object.
	 */
	public $config;
	
	/**
	 * Constructor
	 * 
	 * Instantiates the config object, into which configuration data will be autoloaded.
	 */
	public function __construct()
	{
		$this->config = new Config;
	}
}
