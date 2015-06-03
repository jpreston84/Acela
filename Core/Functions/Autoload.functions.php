<?php
/**
 * Autoload functions.
 */

namespace Acela\Core;

 /**
 * A function to autoload classes and traits.
 * 
 * @param string $class The name of the class or trait that needs to be loaded.
 */
function autoloadClasses($class)
{
	/**
	 * Break the class name into path components.
	 */
	$classNameComponents = explode('\\', $class);

	/**
	 * Look for an appropriate class file in the Acela Core.
	 */
	if(
		$classNameComponents[0] == 'Acela'
		and $classNameComponents[1] == 'Core'
	)
	{
		array_shift($classNameComponents); // Shift off Acela.
		array_shift($classNameComponents); // Shift off Core.

		/**
		 *  Attempt to autoload classes.
		 */
		$filename = __DIR__.'/../Classes/'.implode('/', $classNameComponents).'.class.php'; // Generate a class path like Core/Classes/Core.class.php
		$loaded = autoloadClassFile($filename);
		if($loaded)
		{
			return;
		}

		/**
		 *  Attempt to autoload traits.
		 */
		$filename = __DIR__.'/../Traits/'.implode('/', $classNameComponents).'.trait.php'; // Generate a trait path like Core/Traits/IterateItems.trait.php
		$loaded = autoloadClassFile($filename);
		if($loaded)
		{
			return;
		}
	}
}

/**
 *  A helper function to require the class or trait file once the path has been determined.
 *  
 *  @param string $filename The full path and filename of the file to be required.
 */
function autoloadClassFile($filename)
{
	error_log('Attempting to load '.$filename);
	if(file_exists($filename))
	{
		require_once $filename;
		return true;
	}
	
	return false;
}
