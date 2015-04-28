<?php
/**
 * Autoload functions.
 */

namespace Acela\Core;

 /**
 * A function to autoload classes.
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
			$classNameComponents = array_merge( // Add 'classes' as the second element of the file path.
				array_slice($classNameComponents, 0, 1),
				['classes'],
				array_slice($classNameComponents, 1)
			);
			
			$classNameComponents[count($classNameComponents) - 1] .= '.class.php';
			
			foreach($classNameComponents as $num => $component)
			{
				$classNameComponents[$num] = strtolower($component);
			}
			
			error_log('Loading '.implode('/', $classNameComponents));
			require_once __DIR__.'/../../'.implode('/', $classNameComponents);
		}
	}
}
