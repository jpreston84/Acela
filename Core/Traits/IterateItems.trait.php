<?php
/**
 * Trait for iterating items.
 */

namespace Acela\Core;
 
/**
 * Trait for iterating items.
 * 
 * This class passes Iterator methods on to the ->items property, which must
 * exist and be iterable. A class that uses this trait will need to implement
 * the Iterator interface.
 */
trait IterateItems
{
	/**
	 * Iterator Interface - Rewind
	 * 
	 * Rewind the ->items property to its first element.
	 */
	public function rewind()
	{
		return reset($this->items);
	}

	/**
	 * Iterator Interface - Current
	 * 
	 * Retrieve the current element from the ->items property.
	 */	
	public function current()
	{
		return current($this->items);
	}

	/**
	 * Iterator Interface - Key
	 * 
	 * Get the key of the current element of the ->items property.
	 */
	public function key()
	{
		return key($this->items);
	}
	
	/**
	 * Iterator Interface - Next
	 * 
	 * Advance to the next element in the ->items property.
	 */
	public function next()
	{
		return next($this->items);
	}

	/**
	 * Iterator Interface - Validate
	 * 
	 * Determine if the current element in the ->items property is valid.
	 */
	public function valid()
	{
		return key($this->items) !== null;
	}
}
