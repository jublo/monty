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

/**
 * Monty_MySQLI
 *
 * @package monty
 * @author mynetx
 * @copyright 2011
 * @access public
 */
class Monty_MySQLI extends Monty_Connector
{
    protected $_DB;

    /**
     * Monty_MySQLI::__construct()
     *
     * @return \Monty_MySQLI
     */
    public function __construct()
    {
        $this->_intRows = 0;
        $this->_resQuery = null;
        $this->_strQuery = null;
        $this->_DB = null;
    }

    /**
     * Monty_MySQLI::all()
     *
     * @param int $intType
     * @return array $arrRows
     */
    public function all($intType = MONTY_ALL_ARRAY)
    {
        if (!$this->_strQuery)
        {
            return false;
        }
        $arrRows = array();
        while ($arrRow = $this->next($intType))
        {
            $arrRows[] = $arrRow;
        }
        return $arrRows;
    }


    /**
     * Monty_MySQLI::error()
     *
     * @param int $intType
     * @return mixed $mixError
     */
    public function error($intType = MONTY_ERROR_STRING)
    {
        switch ($intType)
        {
            case MONTY_ERROR_STRING:
                return $this->_DB->error();
            case MONTY_ERROR_ARRAY:
                return array('text' => $this->_DB->error(), 'code' => $this->_DB->errno());
            case MONTY_ERROR_OBJECT:
                $objError = new stdClass;
                $objError->text = $this->_DB->error();
                $objError->code = $this->_DB->errno();
                return $objError;
            case MONTY_ERROR_NUMERIC:
                return $this->_DB->errno();
        }
    }

    /**
     * Monty_MySQLI::id()
     *
     * @return int $intInsertId
     */
    public function id()
    {
        if (!$this->_strQuery)
        {
            return false;
        }
        return $this->_DB->insert_id();
    }

    /**
     * Monty_MySQLI::next()
     *
     * @param int $intType
     * @return mixed $mixRow
     */
    public function next($intType = MONTY_NEXT_ARRAY)
    {
        if (!$this->_strQuery)
        {
            return false;
        }
        switch ($intType)
        {
            case MONTY_NEXT_ARRAY:
                return $this->_resQuery->fetch_assoc();
            case MONTY_NEXT_OBJECT:
                return $this->_resQuery->fetch_object();
        }
    }


    /**
     * Monty_MySQLI::nextfield()
     *
     * @param mixed $mixField
     * @return mixed $mixField
     */
    public function nextfield($mixField = 0)
    {
        if (!$this->_strQuery)
        {
            return false;
        }
        if (is_int($mixField))
        {
            if (!$arrRow = $this->_resQuery->fetch_row())
            {
                return false;
            }
            return isset($arrRow[$mixField]) ? $arrRow[$mixField] : false;
        }
        if (is_string($mixField))
        {
            if (!$arrRow = $this->_resQuery->fetch_assoc())
            {
                return false;
            }
            return isset($arrRow[$mixField]) ? $arrRow[$mixField] : false;
        }
    }

    /**
     * Monty_MySQLI::open()
     *
     * @param string $strUser
     * @param string $strPassword
     * @param string $strDatabase
     * @param string $strHost
     * @return bool $boolIsOpened
     */
    public function open($strUser, $strPassword, $strDatabase, $strHost =
        'localhost', $intOpenType = MONTY_OPEN_NORMAL)
    {
        $strHostString = '';
        switch($intOpenType) {
            case MONTY_OPEN_NORMAL:
                $strHostString = $strHost;
                break;
            case MONTY_OPEN_PERSISTENT:
                $strHostString = 'p:' . $strHost;
                break;
        }
        if (!$this->_DB = new mysqli($strHostString, $strUser, $strPassword, $strDatabase))
        {
            return false;
        }
        return true;
    }

    /**
     * Monty_MySQLI::query()
     *
     * @param string $strQuery
     * @return bool $boolHasSucceeded
     */
    public function query($strQuery)
    {
        $this->_resQuery = null;
        $this->_strQuery = $strQuery;
        if (!$resQuery = @$this->_DB->query($strQuery))
        {
            return false;
        }
        $this->_resQuery = $resQuery;
        $this->_intRows = $resQuery->num_rows;
        return true;
    }

    /**
     * Monty_MySQLI::rows()
     *
     * @return int $intRows
     */
    public function rows()
    {
        if (!$this->_strQuery)
        {
            return false;
        }
        return $this->_intRows;
    }

    /**
     * Monty_MySQLI::seek()
     *
     * @param int $intRow
     * @return bool $boolHasSucceeded
     */
    public function seek($intRow)
    {
        if (!$this->_strQuery)
        {
            return false;
        }
        return $this->_resQuery->data_seek($intRow);
    }

    /**
     * Monty_MySQLI::table()
     *
     * @param string $strTable
     * @param string $strShortcut
     * @return object Monty_MySQLI_Easy
     */
    public function table($strTable, $strShortcut = null)
    {
        return new Monty_MySQLI_Easy($strTable, $strShortcut, $this->_DB);
    }
}
