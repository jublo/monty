<?php

/**
 * monty is a simple database wrapper.
 *
 * @package monty
 * @author J.M. <me@mynetx.net>
 * @copyright 2011-2013 J.M. <me@mynetx.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

/**
 * Monty_Connector
 *
 * @package monty
 * @author mynetx
 * @copyright 2011
 * @access protected
 */
abstract class Monty_Connector
{

    protected $_intRows;
    protected $_resQuery;
    protected $_strQuery;

    /**
     * Monty_Connector::error()
     *
     * Get the last operation error.
     * @param int $intType
     */
    public abstract function error($intType = MONTY_ERROR_STRING);

    /**
     * Monty_Connector::id()
     *
     * Get the last inserted auto-id.
     */
    public abstract function id();

    /**
     * Monty_Connector::next()
     *
     * Walk through the result set.
     */
    public abstract function next();

    /**
     * Monty_Connector::open()
     *
     * Open a database connection.
     * @param string $strUser
     * @param string $strPassword
     * @param string $strDatabase
     * @param string $strHost
     */
    public abstract function open($strUser, $strPassword, $strDatabase, $strHost = 'localhost');

    /**
     * Monty_Connector::query()
     *
     * Run a raw database query.
     * @param string $strQuery
     */
    public abstract function query($strQuery);

    /**
     * Monty_Connector::rows()
     *
     * Get the number of rows in the result set.
     */
    public abstract function rows();

    /**
     * Monty_Connector::seek()
     *
     * Seek a certain row in the result set.
     * @param int $intRow
     */
    public abstract function seek($intRow);

    /**
     * Monty_Connector::nextfield()
     *
     * Walk through the result set and get the next field.
     */
    public abstract function nextfield();
}
