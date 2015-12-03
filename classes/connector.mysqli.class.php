<?php

/**
 * A simple MySQL/MariaDB database wrapper in PHP.
 *
 * @package   Monty
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2011-2015 Jublo Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/jublonet/monty
 */

/**
 * Monty_MySQLI
 *
 * @package   Monty
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2011-2015 Jublo Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/jublonet/monty
 */
class Monty_MySQLI extends Monty_Connector
{
  protected $DB;

  /**
   * Monty_MySQLI::__construct()
   *
   * @return \Monty_MySQLI
   */
  public function __construct()
  {
    $this->number_rows  = 0;
    $this->query_handle = null;
    $this->query_string = null;
    $this->return_type  = MONTY_ALL_ARRAY;
    $this->timeout      = 60;
    $this->DB           = null;
  }

  /**
   * Monty_MySQLI::all()
   *
   * @param int $type The return type
   *
   * @return array $rows_array
   */
  public function all($type = null)
  {
    if (!$this->query_handle) {
      return false;
    }
    $rows_array = array();
    while ($row_array = $this->next($type)) {
      $rows_array[] = $row_array;
    }

    return $rows_array;
  }

  /**
   * Monty_MySQLI::next()
   *
   * @param int $type The return type
   *
   * @return mixed $mixRow
   */
  public function next($type = null)
  {
    if (!$this->query_handle) {
      return false;
    }
    if ($type === null) {
      $type = $this->return_type;
    }
    switch ($type) {
      case MONTY_NEXT_ARRAY:
        return $this->query_handle->fetch_assoc();
      case MONTY_NEXT_OBJECT:
        return $this->query_handle->fetch_object();
    }

    return $this->query_handle->fetch_assoc();
  }

  /**
   * Monty_MySQLI::error()
   *
   * @param int $type The return type
   *
   * @return mixed $mixError
   */
  public function error($type = MONTY_ERROR_STRING)
  {
    switch ($type) {
      case MONTY_ERROR_STRING:
        return $this->DB->error;
      case MONTY_ERROR_ARRAY:
        return array(
          'text' => $this->DB->error,
          'code' => $this->DB->errno
        );
      case MONTY_ERROR_OBJECT:
        $error_object       = new stdClass;
        $error_object->text = $this->DB->error;
        $error_object->code = $this->DB->errno;

        return $error_object;
      case MONTY_ERROR_NUMERIC:
        return $this->DB->errno;
    }

    return $this->DB->error;
  }

  /**
   * Monty_MySQLI::id()
   *
   * @return int $intInsertId
   */
  public function id()
  {
    if (!$this->query_string) {
      return false;
    }

    return $this->DB->insert_id;
  }

  /**
   * Monty_MySQLI::nextfield()
   *
   * @param mixed $field_data Field index to return
   *
   * @return mixed $field_data
   */
  public function nextfield($field_data = 0)
  {
    if (!$this->query_handle) {
      return false;
    }
    if (is_int($field_data)) {
      if (!$row_array = $this->query_handle->fetch_row()) {
        return false;
      }

      return isset($row_array[$field_data]) ? $row_array[$field_data] : false;
    }
    if (is_string($field_data)) {
      if (!$row_array = $this->query_handle->fetch_assoc()) {
        return false;
      }

      return isset($row_array[$field_data]) ? $row_array[$field_data] : false;
    }

    return false;
  }

  /**
   * Monty_MySQLI::open()
   *
   * @param string $user      The database user name
   * @param string $password  The database password
   * @param string $database  Name of the database to connect to
   * @param string $host      optional, Host name of database server
   * @param int    $port      optional, Custom port number
   * @param int    $open_type optional, Whether to open a persistent connection
   *
   * @return bool $boolIsOpened
   */
  public function open($user, $password, $database, $host = 'localhost', $port = 3306, $open_type = MONTY_OPEN_NORMAL)
  {
    $host_connectionstring = '';
    switch ($open_type) {
      case MONTY_OPEN_NORMAL:
        $host_connectionstring = $host;
        break;
      case MONTY_OPEN_PERSISTENT:
        // persistent mysqli connections only for PHP 5.3+
        if (version_compare(phpversion(), '5.3.0', '>=')) {
          $host_connectionstring = 'p:' . $host;
        } else {
          $host_connectionstring = $host;
        }
        break;
    }
    if (!$this->DB = @mysqli_init()) {
      return false;
    }
    if (!$this->DB->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->timeout)) {
      return false;
    }
    if (!$this->DB->real_connect($host_connectionstring, $user, $password, $database, $port)) {
      return false;
    }
    if ($this->DB->connect_error) {
      return false;
    }
    $this->DB->set_charset('utf8');

    return true;
  }

  /**
   * Monty_MySQLI::seek()
   *
   * @param int $row_number The row number to set the pointer to
   *
   * @return bool $boolHasSucceeded
   */
  public function seek($row_number)
  {
    if (!$this->query_handle) {
      return false;
    }

    return $this->query_handle->data_seek($row_number);
  }

  /** Monty_MySQLI::setReturnType()
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
   * Monty_MySQLI::setTimeout()
   * Store default timeout for database connections
   *
   * @param int $timeout The wanted connection timeout in seconds
   */
  public function setTimeout($timeout)
  {
    $this->timeout = $timeout;
  }

  /**
   * Monty_MySQLI::table()
   *
   * @param string $table_name     The table to link to
   * @param string $table_shortcut Optional table shortcut letter
   *
   * @return object Monty_MySQLI_Easy
   */
  public function table($table_name, $table_shortcut = null)
  {
    $easy = new Monty_MySQLI_Easy($table_name, $table_shortcut, $this->DB);
    $easy->setReturnType($this->return_type);

    return $easy;
  }

  /**
   * Monty_MySQLI::tableExists()
   *
   * @param string $table_name The table to check for
   *
   * @return bool $boolExists
   */
  public function tableExists($table_name)
  {
    $this->query('SHOW TABLES LIKE "' . $this->DB->real_escape_string($table_name) . '"');

    return $this->rows() > 0;
  }

  /**
   * Monty_MySQLI::query()
   *
   * @param string $query_string The query to execute
   *
   * @return bool $boolHasSucceeded
   */
  public function query($query_string)
  {
    $this->query_handle = null;
    $this->query_string = $query_string;
    if (!$query_handle = @$this->DB->query($query_string)) {
      return false;
    }
    if ($query_handle === true) {
      return true;
    }
    $this->query_handle = $query_handle;
    $this->number_rows  = $query_handle->num_rows;

    return true;
  }

  /**
   * Monty_MySQLI::rows()
   *
   * @return int $number_rows
   */
  public function rows()
  {
    if (!$this->query_handle) {
      return false;
    }

    return $this->number_rows;
  }
}
