monty
=====
*monty is a simple database wrapper.*

[![Download latest version from GitHub](https://f.cloud.github.com/assets/157944/691044/404c1ca2-db59-11e2-9b0b-8f63de656f9d.png)](https://github.com/mynetx/monty/archive/master.zip)

Copyright (C) 2011-2013 J.M. &lt;me@mynetx.net&gt;

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

- PHP 5.2.0 or higher


First steps
-----------

```php

// load monty
require 'monty/loader.php';

// get the MySQL connector
$objConnector = Monty::getConnector();

// connect to a database
$objConnector->open('youruser', 'fancypass', 'holydatabase');

// not running the database on localhost? add a 4th parameter like this:
// $db->open('youruser', 'fancypass', 'holydatabase', 'pentagon.example.com');

// want persistent connection? add a 5th parameter like this:
// $db->open('youruser', 'fancypass', 'holydatabase', 'pentagon.example.com', MONTY_OPEN_PERSISTENT);

// now there's two operation modes:
// the EASY one first
$objTable = $objConnector->table('themaintable');

// want multiple tables?
// $objTable->add('anothertable');

// set a condition
$objTable->where('field', '=', 'value');

// there are some shortcuts, like this one:
// $objTable->eq('field', 'value');

// you might also want to use ands/ors
// $objTable->or($objTable->eq('field1', 'value1'),
//               $objTable->like('field2', 'value2'));
// equals:
// ... WHERE field1 = "value1" OR field2 LIKE "value2"

// peek at the generated sql code
echo $objTable->sql() . '<br />';

// loop through the results and display them
for($i = 0; $i < $objTable->rows(); $i++) {
    $arrRow = $objTable->next();
    echo $arrRow['field'] . ' = ' . $arrRow['value'] . '<br />';
}

// you could also have got an object instead, like this:
// $objRow = $objTable->next(MONTY_NEXT_OBJECT);
// echo $objRow->field;


// you can also run raw SQL like this (the nerd mode):
$objConnector->query('SELECT * FROM themaintable WHERE field = "value"');
echo $objConnector->rows();

```
