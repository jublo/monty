<?php

/**
 * A simple MySQL/MariaDB database wrapper in PHP.
 *
 * @package   Monty
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2011-2014 Jublo Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/jublonet/monty
 */

define('MONTY_ALL_ARRAY', 1);
define('MONTY_ALL_OBJECT', 2);

define('MONTY_ERROR_STRING', 1);
define('MONTY_ERROR_ARRAY', 2);
define('MONTY_ERROR_OBJECT', 3);
define('MONTY_ERROR_NUMERIC', 4);

define('MONTY_INSERT_NORMAL', 1);
define('MONTY_INSERT_IGNORE', 2);
define('MONTY_INSERT_REPLACE', 3);

define('MONTY_JOIN_NORMAL', 1);
define('MONTY_JOIN_LEFT', 2);
define('MONTY_JOIN_RIGHT', 3);

define('MONTY_NEXT_ARRAY', MONTY_ALL_ARRAY);
define('MONTY_NEXT_OBJECT', MONTY_ALL_OBJECT);

define('MONTY_OPEN_NORMAL', 1);
define('MONTY_OPEN_PERSISTENT', 2);

define('MONTY_QUERY_SELECT', 1);
define('MONTY_QUERY_INSERT', 2);
define('MONTY_QUERY_UPDATE', 3);
define('MONTY_QUERY_DELETE', 4);
define('MONTY_QUERY_TRUNCATE', 5);

define('MONTY_SELECT_NORMAL', 1);
define('MONTY_SELECT_DISTINCT', 2);

/**
 * Monty_Connector
 *
 * @category  Database
 * @package   Monty
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2011 Jublo Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/jublonet/monty
 */
abstract class Monty_Connector
{

    protected $number_rows;
    protected $query_handle;
    protected $query_string;
    protected $return_type;

    /**
     * Monty_Connector::error()
     * Get the last operation error.
     *
     * @param int $type Error message return type
     */
    public abstract function error($type = MONTY_ERROR_STRING);

    /**
     * Monty_Connector::id()
     * Get the last inserted auto-id.
     */
    public abstract function id();

    /**
     * Monty_Connector::next()
     * Walk through the result set.
     */
    public abstract function next();

    /**
     * Monty_Connector::nextfield()
     * Walk through the result set and get the next field.
     */
    public abstract function nextfield();

    /**
     * Monty_Connector::open()
     * Open a database connection.
     *
     * @param string $user      The database user name
     * @param string $password  The database password
     * @param string $database  Name of the database to connect to
     * @param string $host      optional, Host name of database server
     * @param int    $port      optional, Custom port number
     * @param int    $open_type optional, Whether to open a persistent connection
     */
    public abstract function open(
        $user,
        $password,
        $database,
        $host = 'localhost',
        $port = 3306,
        $open_type = MONTY_OPEN_NORMAL
    );

    /**
     * Monty_Connector::query()
     * Run a raw database query.
     *
     * @param string $query_string The SQL query to execute
     */
    public abstract function query($query_string);

    /**
     * Monty_Connector::rows()
     * Get the number of rows in the result set.
     */
    public abstract function rows();

    /**
     * Monty_Connector::seek()
     * Seek a certain row in the result set.
     *
     * @param int $row_number The row number to set the pointer to
     */
    public abstract function seek($row_number);

    /**
     * Monty_Connector::setReturnType()
     * Store default return type for database results
     *
     * @param int $return_type The wanted return type
     */
    public abstract function setReturnType($return_type);

    /**
     * Monty_Connector::table()
     *
     * @param string $table_name     The name of the table to get
     * @param string $table_shortcut Optional shortcut character
     */
    public abstract function table($table_name, $table_shortcut = null);

    /**
     * Monty_Connector::tableExists()
     * Checks whether the given table exists
     *
     * @param string $table_name The name of table to check for
     */
    public abstract function tableExists($table_name);
}
