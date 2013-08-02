<?php

/**
 * monty is a simple database wrapper.
 *
 * PHP version 5
 *
 * @category  Database
 * @package   Monty
 * @author    J.M. <me@mynetx.net>
 * @copyright 2011-2013 J.M. <me@mynetx.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @version   2.3.0
 * @link      https://github.com/mynetx/monty/
 */

define('MONTY_CONNECTOR_MYSQL', 1);
define('MONTY_CONNECTOR_MYSQLI', 2);

/**
 * Monty
 *
 * @category  Database
 * @package   Monty
 * @author    J.M. <me@mynetx.net>
 * @copyright 2013 J.M. <me@mynetx.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/mynetx/monty/
 */

class Monty
{
    protected static $objConnectors = array();

    /**
     * Monty::getConnector()
     * Get the database connector
     * 
     * @param int  $intType      Connector type
     * @param bool $boolExisting Return existing connector of requested type
     * 
     * @return Monty_MySQL|Monty_MySQLI
     */
    public static function getConnector(
        $intType = MONTY_CONNECTOR_MYSQLI,
        $boolExisting = false
    ) {
        // allow simpler default type parameter
        if ($intType === null) {
            $intType = MONTY_CONNECTOR_MYSQLI;
        }

        // if existing connector, look for that first
        if ($boolExisting && isset(self::$objConnectors[$intType])) {
            return self::$objConnectors[$intType];
        }

        switch ($intType) {
        case MONTY_CONNECTOR_MYSQL:
            return new Monty_MySQL;
        case MONTY_CONNECTOR_MYSQLI:
            return new Monty_MySQLI;
        }
    }

    /**
     * Monty::open()
     * 
     * @param string $strUser     The database user name
     * @param string $strPassword The database password
     * @param string $strDatabase Name of the database to connect to
     * @param string $strHost     Host name of database server
     * @param int    $intOpenType Whether to open a persistent connection
     * 
     * @return bool $boolIsOpened
     */
    public static function open(
        $strUser,
        $strPassword,
        $strDatabase,
        $strHost = 'localhost',
        $intOpenType = MONTY_OPEN_NORMAL
    ) {
        if (!isset(self::$objConnectors[MONTY_CONNECTOR_MYSQLI])) {
            self::storeConnector();
        }
        return self::$objConnectors[MONTY_CONNECTOR_MYSQLI]->open(
            $strUser, $strPassword, $strDatabase,
            $strHost, $intOpenType
        );
    }

    /**
     * Monty::storeConnector
     * 
     * @param int $intType Whether to get MySQL or MySQLI connector
     * 
     * @return void
     */
    public static function storeConnector($intType = MONTY_CONNECTOR_MYSQLI)
    {
        self::$objConnectors[$intType] = self::getConnector($intType);
    }

    /**
     * Monty::table
     * 
     * @param string $strTable    Database table to work with
     * @param string $strShortcut Optional table shortcut character
     * 
     * @return Monty_MySQL_Easy|Monty_MySQLI_Easy
     */
    public static function table($strTable, $strShortcut = '')
    {
        if (!isset(self::$objConnectors[MONTY_CONNECTOR_MYSQLI])) {
            self::storeConnector();
        }
        return self::$objConnectors[MONTY_CONNECTOR_MYSQLI]
            ->table($strTable, $strShortcut);
    }

    /**
     * Monty::tableExists()
     * 
     * @param string $strTable Table name to check for existence
     * 
     * @return bool $boolTableExists
     */
    public static function tableExists($strTable)
    {
        if (!isset(self::$objConnectors[MONTY_CONNECTOR_MYSQLI])) {
            self::storeConnector();
        }
        return self::$objConnectors[MONTY_CONNECTOR_MYSQLI]->tableExists($strTable);
    }

    /**
     * Monty::setReturnType
     * 
     * @param int $returnType The return type to set
     *
     * @return void
     */
    public static function setReturnType($returnType)
    {
        if (!isset(self::$objConnectors[MONTY_CONNECTOR_MYSQLI])) {
            self::storeConnector();
        }
        return self::$objConnectors[MONTY_CONNECTOR_MYSQLI]
            ->setReturnType($returnType);
    }
}
