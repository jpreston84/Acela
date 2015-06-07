<?php
/**
 * A test for the new Schema object.
 */

namespace Acela\Application;

use \Acela\Core;
 
require_once __DIR__.'/../Core/Core.php';

echo '<h1>Schema Test</h1>';

/* $table = Core\Schema::get('users');

// $table->get('userCreatedOn')->nonNullable();

$table->get('newInteger')->delete();
$table->get('newBigInteger')->delete();
$table->get('newAutoIncrement')->delete();
$table->deleteField('unsignedBigInt');
$table->deleteField('nullableInteger');

// $table->deleteField('userId');
// $table->deleteField('userCreatedOn');
// $table->deleteField('userCreatedBy');
// $table->deleteField('userModifiedOn');
// $table->deleteField('userModifiedBy');
// $table->deleteField('userFirstName');
// $table->deleteField('userLastName');

$table->delete(); // Flag the table for deletion.

$table->save(); */

/**
 *  Create a new table.
 */
$table = Core\Schema::createTable('what');
$table->bigint('foo');
$table->bigint('id')->primary()->autoIncrement();

$table->save();

echo '<br /><b>Done.</b>';