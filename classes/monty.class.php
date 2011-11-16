<?php

/**
 * monty is a simple database wrapper.
 * Copyright (C) 2011 mynetx.

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.

 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('MONTY_CONNECTOR_MYSQL', 1);

class Monty
{
    public static function getConnector($intType = MONTY_CONNECTOR_MYSQL)
    {
        switch ($intType)
        {
            case MONTY_CONNECTOR_MYSQL:
                return new Monty_MySQL;
        }
    }

    public static function table($strTable, $strShortcut = '')
    {
        return self::getConnector()->table($strTable, $strShortcut);
    }
}
