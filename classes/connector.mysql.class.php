<?php

/**
 * monty is a simple database wrapper.
 *
 * PHP version 5
 *
 * @category  Database
 * @package   Monty
 * @author    Jublo IT Solutions <support@jublo.net>
 * @copyright 2011-2014 Jublo IT Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/jublonet/monty
 */

/**
 * Monty_MySQL
 *
 * @category   Database
 * @package    Monty
 * @author     Jublo IT Solutions <support@jublo.net>
 * @copyright  2011 Jublo IT Solutions <support@jublo.net>
 * @license    http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link       https://github.com/jublonet/monty
 * @deprecated since 2.2.0
 */
class Monty_MySQL extends Monty_Connector
{
    /**
     * Monty_MySQL::__construct()
     *
     * @return \Monty_MySQL
     */
    public function __construct()
    {
        $this->number_rows = 0;
        $this->query_handle = null;
        $this->query_string = null;
        $this->return_type = MONTY_ALL_ARRAY;
    }

    /**
     * Monty_MySQL::all()
     *
     * @param int $type The return type
     *
     * @return array $rows_array
     */
    public function all($type = null)
    {
        if (! $this->query_handle) {
            return false;
        }
        $rows_array = array();
        while ($row_array = $this->next($type)) {
            $rows_array[] = $row_array;
        }
        return $rows_array;
    }


    /**
     * Monty_MySQL::error()
     *
     * @param int $type The return type
     *
     * @return mixed $mixError
     */
    public function error($type = MONTY_ERROR_STRING)
    {
        switch ($type) {
        case MONTY_ERROR_STRING:
            return mysql_error();
        case MONTY_ERROR_ARRAY:
            return array(
                'text' => mysql_error(),
                'code' => mysql_errno()
            );
        case MONTY_ERROR_OBJECT:
            $error_object = new stdClass;
            $error_object->text = mysql_error();
            $error_object->code = mysql_errno();
            return $error_object;
        case MONTY_ERROR_NUMERIC:
            return mysql_errno();
        }
    }

    /**
     * Monty_MySQL::id()
     *
     * @return int $intInsertId
     */
    public function id()
    {
        if (! $this->query_string) {
            return false;
        }
        return mysql_insert_id();
    }

    /**
     * Monty_MySQL::next()
     *
     * @param int $type The return type
     *
     * @return mixed $mixRow
     */
    public function next($type = null)
    {
        if (! $this->query_handle) {
            return false;
        }
        if ($type === null) {
            $type = $this->return_type;
        }
        switch ($type) {
        case MONTY_NEXT_ARRAY:
            return mysql_fetch_assoc($this->query_handle);
        case MONTY_NEXT_OBJECT:
            return mysql_fetch_object($this->query_handle);
        }
    }


    /**
     * Monty_MySQL::nextfield()
     *
     * @param mixed $field_data Optional result column name/index
     *
     * @return mixed $field_data
     */
    public function nextfield($field_data = 0)
    {
        if (! $this->query_handle) {
            return false;
        }
        if (is_int($field_data)) {
            if (! $row_array = mysql_fetch_row($this->query_handle)) {
                return false;
            }
            return isset($row_array[$field_data]) ? $row_array[$field_data] : false;
        }
        if (is_string($field_data)) {
            if (!$row_array = mysql_fetch_assoc($this->query_handle)) {
                return false;
            }
            return isset($row_array[$field_data]) ? $row_array[$field_data] : false;
        }
    }

    /**
     * Monty_MySQL::open()
     *
     * @param string $user      The database user name
     * @param string $password  The database password
     * @param string $database  Name of the database to connect to
     * @param string $host      Host name of database server
     * @param int    $open_type Whether to open a persistent connection
     *
     * @return bool $boolIsOpened
     */
    public function open(
        $user,
        $password,
        $database,
        $host = 'localhost',
        $open_type = MONTY_OPEN_NORMAL
    ) {
        $open_function = '';
        switch ($open_type) {
        case MONTY_OPEN_NORMAL:
            $open_function = 'mysql_connect';
            break;
        case MONTY_OPEN_PERSISTENT:
            $open_function = 'mysql_pconnect';
            break;
        }
        if (! @$open_function($host, $user, $password)) {
            return false;
        }
        if (! @mysql_select_db($database)) {
            return false;
        }
        @mysql_set_charset('utf8');
        return true;
    }

    /**
     * Monty_MySQL::query()
     *
     * @param string $query_string The SQL query to execute
     *
     * @return bool $boolHasSucceeded
     */
    public function query($query_string)
    {
        $this->query_handle = null;
        $this->query_string = $query_string;
        if (! $query_handle = @mysql_query($query_string)) {
            return false;
        }
        $this->query_handle = $query_handle;
        $this->number_rows = @mysql_num_rows($query_handle);
        return true;
    }

    /**
     * Monty_MySQL::rows()
     *
     * @return int $number_rows
     */
    public function rows()
    {
        if (! $this->query_handle) {
            return false;
        }
        return $this->number_rows;
    }

    /**
     * Monty_MySQL::seek()
     *
     * @param int $row_number The row to set the result pointer to
     *
     * @return bool $boolHasSucceeded
     */
    public function seek($row_number)
    {
        if (!$this->query_handle) {
            return false;
        }
        return mysql_data_seek($this->query_handle, $row_number);
    }

    /**
     * Monty_MySQL::setReturnType()
     *
     * @param int $return_type The return type to set as default
     *
     * @return void
     */
    public function setReturnType($return_type)
    {
        $this->return_type = $return_type;
    }

    /**
     * Monty_MySQL::table()
     *
     * @param string $table_name     The table name
     * @param string $table_shortcut Optional table shortcut character
     *
     * @return object Monty_MySQL_Easy
     */
    public function table($table_name, $table_shortcut = null)
    {
        $easy = new Monty_MySQL_Easy($table_name, $table_shortcut);
        $easy->setReturnType($this->return_type);
        return $easy;
    }

    /**
     * Monty_MySQL::tableExists()
     *
     * @param string $table_name The table to check for existence
     *
     * @return bool $boolExists
     */
    public function tableExists($table_name)
    {
        $this->query(
            'SHOW TABLES LIKE "' . mysql_real_escape_string($table_name) . '"'
        );
        return $this->rows() > 0;
    }
}
