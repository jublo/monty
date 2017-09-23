<?php

/**
 * A simple MySQL/MariaDB database wrapper in PHP.
 *
 * @package   Monty
 * @version   2.4.2
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2011-2017 Jublo Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/jublonet/monty
 */

define('MONTY_CONNECTOR_MYSQLI', 2);

/**
 * Monty
 *
 * @package   Monty
 * @author    Jublo Solutions <support@jublo.net>
 * @copyright 2011-2017 Jublo Solutions <support@jublo.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/jublonet/monty
 */

class Monty
{
  protected static $objConnectors = [];

  /**
   * Monty::getConnector()
   * Get the database connector
   *
   * @param int  $type         Connector type
   * @param bool $boolExisting Return existing connector of requested type
   *
   * @return Monty_MySQL|Monty_MySQLI
   */
  public static function getConnector($type = MONTY_CONNECTOR_MYSQLI, $boolExisting = false)
  {
    // allow simpler default type parameter
    if ($type === null) {
      $type = MONTY_CONNECTOR_MYSQLI;
    }

    // if existing connector, look for that first
    if ($boolExisting && isset(self::$objConnectors[$type])) {
      return self::$objConnectors[$type];
    }

    /*
    switch ($type) {
    case MONTY_CONNECTOR_MYSQL:
    return new Monty_MySQL;
    }
    */

    return new Monty_MySQLI;
  }

  /**
   * Monty::open()
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
  public static function open($user, $password, $database, $host = 'localhost', $port = 3306, $open_type = MONTY_OPEN_NORMAL)
  {
    if (!isset(self::$objConnectors[MONTY_CONNECTOR_MYSQLI])) {
      self::storeConnector();
    }

    return self::$objConnectors[MONTY_CONNECTOR_MYSQLI]->open($user, $password, $database, $host, $port, $open_type);
  }

  /**
   * Monty::storeConnector
   *
   * @param int $type Whether to get MySQL or MySQLI connector
   *
   * @return void
   */
  public static function storeConnector($type = MONTY_CONNECTOR_MYSQLI)
  {
    self::$objConnectors[$type] = self::getConnector($type);
  }

  /**
   * Monty::setReturnType
   *
   * @param int $returnType The return type to set
   *
   * @return int $old_return_type
   */
  public static function setReturnType($returnType)
  {
    if (!isset(self::$objConnectors[MONTY_CONNECTOR_MYSQLI])) {
      self::storeConnector();
    }

    return self::$objConnectors[MONTY_CONNECTOR_MYSQLI]->setReturnType($returnType);
  }

  /**
   * Monty::setTimeout
   *
   * @param int $timeout The connection timeout to set
   *
   * @return void
   */
  public static function setTimeout($timeout)
  {
    if (!isset(self::$objConnectors[MONTY_CONNECTOR_MYSQLI])) {
      self::storeConnector();
    }

    self::$objConnectors[MONTY_CONNECTOR_MYSQLI]->setTimeout($timeout);
  }

  /**
   * Monty::table
   *
   * @param string $table_name     Database table to work with
   * @param string $table_shortcut Optional table shortcut character
   *
   * @return Monty_MySQL_Easy | Monty_MySQLI_Easy
   */
  public static function table($table_name, $table_shortcut = '')
  {
    if (!isset(self::$objConnectors[MONTY_CONNECTOR_MYSQLI])) {
      self::storeConnector();
    }

    return self::$objConnectors[MONTY_CONNECTOR_MYSQLI]->table($table_name, $table_shortcut);
  }

  /**
   * Monty::tableExists()
   *
   * @param string $table_name Table name to check for existence
   *
   * @return bool $boolTableExists
   */
  public static function tableExists($table_name)
  {
    if (!isset(self::$objConnectors[MONTY_CONNECTOR_MYSQLI])) {
      self::storeConnector();
    }

    return self::$objConnectors[MONTY_CONNECTOR_MYSQLI]->tableExists($table_name);
  }
}
