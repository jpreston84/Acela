<?php
/**
 * The main ACLI class.
 */

namespace Acela\Core\ACLI;

use \Acela\Core as Core;

class ACLI
{	
	/**
	 * Run ACLI.
	 */
	public function run()
	{
		$this->displayWelcome();

		/**
		 * Look for command.
		 */
		if(empty($GLOBALS['argv'][1]))
		{
			$this->displayUsage();
			return;
		}

		/**
		 * Run command.
		 */
		$commandClass = __NAMESPACE__.'\Commands\\'.$GLOBALS['argv'][1];
		$command = new $commandClass;
		$command->run(array_slice($GLOBALS['argv'], 2));
	}
	
	private function displayWelcome()
	{
		echo 'Acela CLI'.PHP_EOL;
	}
	
	private function displayUsage()
	{
		echo 'Usage: ./acli [command] [options]'.PHP_EOL;
		echo 'Available commands:'.PHP_EOL;
		
		foreach(glob(__DIR__.'/Commands/*.class.php') as $filename)
		{
			/**
			 * Get the name of the command.
			 */
			$filename = explode('/', $filename);
			$filename = end($filename);
			$filename = substr($filename, 0, -10);
			
			$className = __NAMESPACE__.'\Commands\\'.$filename;
			$usage = $className::getUsage();
			
			echo '  '.str_pad($usage['command'], 40).$usage['description'].PHP_EOL;
		}
	}
}