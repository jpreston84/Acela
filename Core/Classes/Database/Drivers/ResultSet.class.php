<?php
/**
 * A template for database result sets.
 */

namespace Acela\Core\Database\Drivers;

/**
 * Template for database result sets.
 */
abstract class ResultSet implements \Countable, \Iterator
{
	/**
	 * @var $driver A reference to the database driver being used.
	 */
	protected $driver;
}