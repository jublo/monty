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
 * Monty_MySQL
 *
 * @package monty
 * @author mynetx
 * @copyright 2011
 * @access public
 */
class Monty_MySQL extends Monty_Connector
{
	/**
	 * Monty_MySQL::__construct()
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->_intRows = 0;
		$this->_resQuery = null;
		$this->_strQuery = null;
	}

	/**
	 * Monty_MySQL::error()
	 *
	 * @param int $intType
	 * @return mixed $mixError
	 */
	public function error($intType = MONTY_ERROR_STRING)
	{
		switch ($intType) {
			case MONTY_ERROR_STRING:
				return mysql_error();
			case MONTY_ERROR_ARRAY:
				return array('text' => mysql_error(), 'code' => mysql_errno());
			case MONTY_ERROR_OBJECT:
				$objError = new stdClass;
				$objError->text = mysql_error();
				$objError->code = mysql_errno();
				return $objError;
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
		if (!$this->_strQuery) {
			return false;
		}
		return mysql_insert_id();
	}

	/**
	 * Monty_MySQL::next()
	 *
	 * @param int $intType
	 * @return mixed $mixRow
	 */
	public function next($intType = MONTY_NEXT_ARRAY) {
		if (!$this->_strQuery) {
			return false;
		}
		switch($intType) {
			case MONTY_NEXT_ARRAY:
				return mysql_fetch_assoc($this->_resQuery);
			case MONTY_NEXT_OBJECT:
				return mysql_fetch_object($this->_resQuery);
		}
	}


	/**
	 * Monty_MySQL::nextfield()
	 *
	 * @param mixed $mixField
	 * @return mixed $mixField
	 */
	public function nextfield($mixField = 0) {
		if (!$this->_strQuery) {
			return false;
		}
		if(is_int($mixField)) {
			if(!$arrRow = mysql_fetch_row($this->_resQuery)) {
				return false;
			}
			return isset($arrRow[$mixField]) ? $arrRow[$mixField] : false;
		}
		if(is_string($mixField)) {
			if(!$arrRow = mysql_fetch_assoc($this->_resQuery)) {
				return false;
			}
			return isset($arrRow[$mixField]) ? $arrRow[$mixField] : false;
		}
	}
	/**
	 * Monty_MySQL::open()
	 *
	 * @param string $strUser
	 * @param string $strPassword
	 * @param string $strDatabase
	 * @param string $strHost
	 * @return bool $boolIsOpened
	 */
	public function open($strUser, $strPassword, $strDatabase, $strHost =
		'localhost')
	{
		if (!@mysql_connect($strHost, $strUser, $strPassword)) {
			return false;
		}
		if (!@mysql_select_db($strDatabase)) {
			return false;
		}
		return true;
	}

	/**
	 * Monty_MySQL::query()
	 *
	 * @param string $strQuery
	 * @return bool $boolHasSucceeded
	 */
	public function query($strQuery)
	{
		$this->_resQuery = null;
		$this->_strQuery = $strQuery;
		if (!$resQuery = @mysql_query($strQuery)) {
			return false;
		}
		$this->_resQuery = $resQuery;
		$this->_intRows = @mysql_num_rows($resQuery);
		return true;
	}

	/**
	 * Monty_MySQL::rows()
	 *
	 * @return int $intRows
	 */
	public function rows()
	{
		if (!$this->_strQuery) {
			return false;
		}
		return $this->_intRows;
	}

	/**
	 * Monty_MySQL::seek()
	 *
	 * @param int $intRow
	 * @return bool $boolHasSucceeded
	 */
	public function seek($intRow) {
		if (!$this->_strQuery) {
			return false;
		}
		return mysql_data_seek($intRow);
	}
}
