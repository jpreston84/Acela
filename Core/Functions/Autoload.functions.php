<?php
/**
 * Autoload functions.
 */

namespace Acela\Core;

 /**
 * A function to autoload classes and traits.
 * 
 * @param string $class The name of the class that needs to be loaded.
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
	)
	{
		array_shift($classNameComponents); // Shift off Acela.

		if($classNameComponents[0] == 'Core')
		{
			/**
			 * Attempt to autoload class from classes folder.
			 */
			$classNameComponents = array_merge( // Add 'classes' as the second element of the file path.
				array_slice($classNameComponents, 0, 1),
				['Classes'],
				array_slice($classNameComponents, 1)
			);
			$classNameComponents[count($classNameComponents) - 1] .= '.class.php';
			$filename = __DIR__.'/../../'.implode('/', $classNameComponents);
			if(file_exists($filename))
			{
				require_once $filename;
				return;
			}
			
			/**
			 * Attempt to autoload trait from traits folder.
			 */
			$classNameComponents = array_merge( // Add 'Traits' as the second element of the file path.
				array_slice($classNameComponents, 0, 1),
				['Traits'],
				array_slice($classNameComponents, 1)
			);
			$classNameComponents[count($classNameComponents) - 1] .= '.trait.php';
			$filename = __DIR__.'/../../'.implode('/', $classNameComponents);
			if(file_exists($filename))
			{
				require_once $filename;
				return;
			}
		}
	}
}
