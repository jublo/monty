monty
=====
*A simple MySQL/MariaDB database wrapper in PHP.*

Copyright (C) 2011-2015 Jublo Solutions <support@jublo.net>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published
by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

### Requirements

- PHP 5.5.0 or higher


First steps
-----------

```php

// load monty
require 'monty/loader.php';

// get the MySQL connector
$connector = Monty::getConnector();

// connect to a database
$connector->open('youruser', 'fancypass', 'holydatabase');

// not running the database on localhost? add a 4th parameter like this:
// $db->open('youruser', 'fancypass', 'holydatabase', 'pentagon.example.com');

// need a custom port number? add a 5th parameter like this:
// $db->open(
//   'youruser', 'fancypass', 'holydatabase',
//   'pentagon.example.com', 3307
// );

// want a persistent connection? add a 6th parameter like this:
// $db->open(
//   'youruser', 'fancypass', 'holydatabase',
//   'pentagon.example.com', 3307, MONTY_OPEN_PERSISTENT
// );

// now there's two operation modes:
// the EASY one first
$table = $connector->table('themaintable');

// want multiple tables?
// $table->add('anothertable');

// set a condition
$table->where('field', '=', 'value');

// there are some shortcuts, like this one:
// $table->eq('field', 'value');

// switching to DISTINCT results is possible, too:
// $table->select(MONTY_SELECT_DISTINCT);

// you might also want to use ands/ors
// $table->or(
//   $table->eq('field1', 'value1'),
//   $table->like('field2', 'value2')
// );
// equals:
// ... WHERE field1 = "value1" OR field2 LIKE "value2"

// peek at the generated sql code without executing it
echo $table->sql() . '<br />';

// loop through the results and display them
for($i = 0; $i < $table->rows(); $i++) {
  $row_array = $table->next();
  echo $row_array['field'] . ' = ' . $row_array['value'] . '<br />';
}

// you could also have got an object instead, like this:
// $row = $table->next(MONTY_NEXT_OBJECT);
// echo $row->field;

// for setting the object return type as default, put this statement
// at the top of your code:
// $table->setReturnType(MONTY_ALL_OBJECT);


// you can also run raw SQL like this (the nerd mode):
$connector->query('SELECT * FROM themaintable WHERE field = "value"');
echo $connector->rows();

// check if a certain table exists at the moment:
if ($connector->tableExists('the_table')) {
  // do something
}

// update values
$values = [
  'column1' => 'Test',
  'column2' => 12345
];
$table->update($values);

// update a single value
$table->update('column1', 'Test');

// update by using the content of another field
// like: SET column2 = column1
$table->update('column2', ['column1']); // note the array syntax for value

```
