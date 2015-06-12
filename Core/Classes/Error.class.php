<?php
/**
 * A class for handling errors.
 */

namespace Acela\Core;

use \Monolog;

/**
 * Define error level constants.
 */
define('ERROR_DEBUG', 100);
define('ERROR_INFO', 200);
define('ERROR_NOTICE', 250);
define('ERROR_WARNING', 300);
define('ERROR_ERROR', 400);
define('ERROR_CRITICAL', 500);
define('ERROR_ALERT', 550);
define('ERROR_EMERGENCY', 600);

/**
 * A class for handling errors.
 */
class Error
{
	/**
	 *  Use GlobalInstance functionality.
	 */
	use GlobalInstance;

	/**
	 * @var Monolog\Logger $log A Monolog Logger instance.
	 */
	private $log = null;
	
	/**
	 * @var \stdClass $config A configuration for the error handler.
	 */
	private $config = null;
	
	/**
	 * Constructor - Creates the Monolog logger instance.
	 */
	public function __construct($name = null)
	{
		/**
		 * Set default name.
		 */
		if(is_null($name))
		{
			$name = 'default';
		}
		
		$this->config = $GLOBALS['core']->config->error->$name;
		
		$this->log = new Monolog\Logger($this->config->name);
		$handler = new Monolog\Handler\StreamHandler($this->config->path, Monolog\Logger::WARNING);
		$handler->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true));
		$this->log->pushHandler($handler);
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
		if(substr($name, 0, 3) == 'add') // If a method name like ->addWarning() was passed...
		{
			$name = substr($name, 3);
			$errorLevel = $this->getErrorLevel($name); // Get the numeric error level for the name provided.
			if($errorLevel) // If this is a real error level, we should call ->addMessage() instead...
			{
				array_unshift($arguments, $name);
				$name = 'addMessage';
			}
		}
		elseif($this->getErrorLevel($name)) // If the function name passed is in fact an error level...
		{
			array_unshift($arguments, $name);
			$name = 'addMessage';
		}
		elseif($name === 'phpError')
		{
			// Do nothing -- just pass the call through.
		}
		else
		{
			$this->critical('The error handler does not have the method "'.$name.'".');
		}
		
		return call_user_func_array([$this, $name], $arguments);
	}

	/**
	 * Get the numeric error level (via constant) for a particular error type.
	 * 
	 * @param string $name The name of the error level to get a value for.
	 * @return mixed The numeric error level, or false if undefined.
	 */	
	private function getErrorLevel($name)
	{
		$constantName = 'ERROR_'.strtoupper($name); // The name of the approrpiate constant that should exist for this error level.
		if(defined($constantName))
		{
			return constant($constantName);
		}
		return false;
	}
	
	/**
	 * Add a message to the log.
	 * 
	 * @param string $level The level of the message. Possible values are: debug,
	 * info, notice, warning, error, critical, alert, emergency 
	 * @param string $message The error message to log.
	 * @param array $additionalParameters An array of additional parameters to log.
	 */
	private function addMessage($level, $message, $num = null, $additionalParameters = [])
	{
		$levelNum = $this->getErrorLevel($level);
		$methodName = 'add'.ucwords($level);
		
		/**
		 * Add error number if applicable.
		 */
		if(!empty($num))
		{
			$message = 'Error #'.$num.' - '.$message;
			$additionalParameters['errorNo'] = $num;
		}
		
		/**
		 * If there's a critical error or higher, add a backtrace.
		 */
		if($levelNum >= ERROR_CRITICAL)
		{
			$additionalParameters['backtrace'] = debug_backtrace(false);
			
			/**
			 *  Clean up the backtrace.
			 *  
			 *  We want to remove all backtrace elements relevant to the error logger.
			 */
			foreach($additionalParameters['backtrace'] as $key => $param)
			{
				if(
					(!empty($param['file']) and $param['file'] == __FILE__) // If this part of the backtrace took place in the error logger file...
					or (!empty($param['class']) and $param['class'] == get_class()) // Or this part of the backtrace took place in the \Acela\Core\Error class (this class)...
				)
				{
					unset($additionalParameters['backtrace'][$key]);
				}
			}
		}
		
		$message .= PHP_EOL.'Extra Data:'.PHP_EOL.print_r($additionalParameters, true);
		
		$this->log->$methodName($message);
		
		/**
		 *  If in CLI mode, echo the error to the console.
		 */
		if(ACELA_MODE === 'cli')
		{
			echo $message.PHP_EOL;
		}
		
		/**
		 * Terminate execution if this is critical or higher.
		 */
		if($levelNum >= ERROR_CRITICAL)
		{
			// To Do -- redirect the user to a critical error page.
			die();
		}
	}
	
	/**
	 * Handle errors generated by PHP.
	 * 
	 * @param int $errNo The PHP error number.
	 * @param string $errStr The PHP error string.
	 * @param string $errFile The name of the file the error occurred in.
	 * @param int $errLine The line number the error occurred on.
	 * @return bool Returns true if this function handled the error, false otherwise.
	 */
	private function phpError($errNo, $errStr, $errFile, $errLine)
	{
		$this->addCritical('PHP error #'.$errNo.' - '.$errStr, null, ['file' => $errFile, 'line' => $errLine]);
		
		return true;
	}
}
