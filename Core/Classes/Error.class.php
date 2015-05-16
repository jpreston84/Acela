<?php
/**
 * A class for handling errors.
 */

namespace Acela\Core;

/**
 * A class for handling errors.
 */
class Error extends GlobalInstance
{
	/**
	 * @var Monolog\Logger $log A Monolog Logger instance.
	 */
	private $log = null;
	
	/**
	 * Constructor - Creates the Monolog logger instance.
	 */
	public function __construct()
	{
		$log = new Monolog\Logger('name');
		$log->pushHandler(new Monolog\StreamHandler(__DIR__.'../../Logs/general.log', Monolog\Logger::WARNING));
	}
	
	/**
	 * Magic Method - Call static function. When a method is called statically, the
	 * global instance is located, and the function call is passed on.
	 * 
	 * @param string $name The name of the method to call.
	 * @param array $arguments Parameters for the call.
	 * @return mixed The results of the call.
	 */
	static function __callStatic($name, $arguments)
	{
		$instance = static::getInstance();
		return call_user_func_array([$instance, $name], $arguments);
	}
	
	/**
	 * Magic method - Call functions. This is needed because of __callStatic.
	 * 
	 * @param string $name The name of the method to call.
	 * @param array $arguments Parameters for the call.
	 * @return mixed The results of the call.
	 */
	public function __call($name, $arguments)
	{
		if(in_array($name, [ 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency' ]))
		{
			$name = 'addMessage';
			array_unshift($arguments, $name);
		}
		
		return call_user_func_array([$this, $name], $arguments);
	}
	
	/**
	 * Add a message to the log.
	 * 
	 * @param string $level The level of the message. Possible values are: debug,
	 * info, notice, warning, error, critical, alert, emergency 
	 * @param string $message The error message to log.
	 * @param array $additionalParameters An array of additional parameters to log.
	 */
	private function addMessage($level, $message, $additionalParameters)
	{
		$methodName = 'add'.ucwords($level);
		
		$this->log->$methodName($message, $additionalParameters);
	}
}
