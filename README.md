**monty is a simple database wrapper.**

https://github.com/mynetx/monty

---

*This project is licensed under the GNU Lesser General Public License V3.
Find more details in the LICENSE file.
You must not redistribute the project files without README and LICENSE.*

---

System requirements
-------------------

To use this database wrapper, make sure the web server is running
at least PHP 5.2, or any higher version.


First steps
-----------

<pre lang="php"><code>

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

</code></pre>