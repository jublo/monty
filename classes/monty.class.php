<?php

/**
 * monty is a simple database wrapper.
 *
 * @package monty
 * @version 2.2.0-dev
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

define('MONTY_CONNECTOR_MYSQL', 1);
define('MONTY_CONNECTOR_MYSQLI', 2);

class Monty
{
    protected static $_objConnector = null;

    public static function getConnector($intType = MONTY_CONNECTOR_MYSQLI)
    {
        switch ($intType)
        {
            case MONTY_CONNECTOR_MYSQL:
                return new Monty_MySQL;
            case MONTY_CONNECTOR_MYSQLI:
                return new Monty_MySQLI;
        }
    }

    public static function open($strUser, $strPassword, $strDatabase,
        $strHost = 'localhost', $intOpenType = MONTY_OPEN_NORMAL)
    {
        if (!self::$_objConnector)
        {
            self::storeConnector();
        }
        return self::$_objConnector->open(
            $strUser, $strPassword, $strDatabase,
            $strHost, $intOpenType);
    }

    public static function storeConnector($intType = MONTY_CONNECTOR_MYSQLI)
    {
        self::$_objConnector = self::getConnector($intType);
    }

    public static function table($strTable, $strShortcut = '')
    {
        if (!self::$_objConnector)
        {
            self::storeConnector();
        }
        return self::$_objConnector->table($strTable, $strShortcut);
    }
}
