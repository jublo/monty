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
 * Monty_MySQL_Easy
 *
 * @package monty
 * @author mynetx
 * @copyright 2011
 * @access public
 */
class Monty_MySQL_Easy extends Monty_MySQL
{
	protected static $_arrComparisons;
	protected $_arrFields;
	protected $_arrJoins;
	protected $_arrSorts;
	protected $_arrTable;
	protected $_arrWheres;
	protected $_boolDirty;
	protected $_intInsertType;
	protected $_intLimitCount;
	protected $_intLimitStart;

	/**
	 * Monty_MySQL_Easy::__construct()
	 *
	 * @param string $strTable
	 * @param string $strShortcut
	 * @return void
	 */
	public function __construct($strTable, $strShortcut = null)
	{
		parent::__construct();
		if (!$strShortcut) {
			$strShortcut = substr($strTable, 0, 1);
		}
		self::$_arrComparisons = array('eq' => '=', 'gt' => '>', 'gte' => '>=', 'like' =>
			'LIKE', 'lt' => '<', 'lte' => '<=', 'ne' => '!=', 'regexp' => 'REGEXP');
		$this->_arrJoins = array();
		$this->_arrTable = array($strTable, $strShortcut);
		$this->_arrWheres = array();
		$this->_boolDirty = true;
		$this->_intInsertType = null;
		$this->_intLimitCount = null;
		$this->_intLimitStart = null;
	}

	/**
	 * Monty_MySQL_Easy::__call()
	 *
	 * @param string $strMethod
	 * @param array $arrParams
	 * @return mixed $mixReturn
	 */
	public function __call($strMethod, $arrParams)
	{
		if (substr($strMethod, 0, 1) == '_') {
			trigger_error("$strMethod is not a public method.", E_USER_ERROR);
			return;
		}
		if (in_array($strMethod, array_keys(self::$_arrComparisons))) {
			return $this->where($arrParams[0], self::$_arrComparisons[$strMethod], $arrParams[1]);
		}
		trigger_error("$strMethod is not a method.", E_USER_ERROR);
	}

	/**
	 * Monty_MySQL_Easy::all()
	 *
	 * @param int $intType
	 * @return array $arrRows
	 */
	public function all($intType = MONTY_NEXT_ARRAY)
	{
		$this->_buildQuery();
		return parent::all($intType);
	}

	/**
	 * Monty_MySQL_Easy::insert()
	 *
	 * @param array $arrFields
	 * @param int $intType
	 * @return bool $boolHasSucceeded
	 */
	public function insert($arrFields, $intType = MONTY_INSERT_NORMAL)
	{
		$this->_arrFields = $arrFields;
		$this->_boolDirty = true;
		$this->_intInsertType = $intType;
		return $this->_buildQuery(MONTY_QUERY_INSERT);
	}

	/**
	 * Monty_MySQL_Easy::join()
	 *
	 * @param string $strTable
	 * @param string $strShortcut
	 * @param int $intJoin
	 * @return void
	 */
	public function join($strTable, $strShortcut, $intJoin = MONTY_JOIN_NORMAL)
	{
		$this->_boolDirty = true;
		$this->_arrJoins[] = array($strTable, $intJoin);
	}

	/**
	 * Monty_MySQL_Easy::limit()
	 *
	 * @param int $intStart
	 * @param int $intCount
	 * @return void
	 */
	public function limit()
	{
		$this->_boolDirty = true;
		if (func_num_args() == 1) {
			$this->_intLimitCount = func_get_arg(0);
			$this->_intLimitStart = 0;
		} elseif (func_num_args() == 2) {
			$this->_intLimitCount = func_get_arg(1);
			$this->_intLimitStart = func_get_arg(0);
		}
	}

	/**
	 * Monty_MySQL_Easy::next()
	 *
	 * @param int $intType
	 * @return mixed $mixRow
	 */
	public function next($intType = MONTY_NEXT_ARRAY)
	{
		$this->_buildQuery();
		return parent::next($intType);
	}

	/**
	 * Monty_MySQL_Easy::nextfield()
	 *
	 * @param mixed $mixField
	 * @return mixed $mixField
	 */
	public function nextfield($mixField = 0)
	{
		$this->_buildQuery();
		return parent::nextfield($mixField = 0);
	}

	/**
	 * Monty_MySQL_Easy::rand()
	 *
	 * @return void
	 */
	public function rand()
	{
		$this->_boolDirty = true;
		$this->_arrSorts = array(array(null, 1));
	}

	/**
	 * Monty_MySQL_Easy::rows()
	 *
	 * @return int $intRows
	 */
	public function rows()
	{
		$this->_buildQuery();
		return parent::rows();
	}

	/**
	 * Monty_MySQL_Easy::seek()
	 *
	 * @param int $intRow
	 * @return bool $boolHasSucceeded
	 */
	public function seek($intRow)
	{
		$this->_buildQuery();
		return parent::seek($intRow);
	}

	/**
	 * Monty_MySQL_Easy::sort()
	 *
	 * @param string $strBy
	 * @param int $intAsc
	 * @return void
	 */
	public function sort($strBy, $intAsc = 1)
	{
		$this->_boolDirty = true;
		$this->_arrSorts[] = array($strBy, $intAsc);
	}

	/**
	 * Monty_MySQL_Easy::sql()
	 *
	 * @param int $intType
	 * @return string $strQuery
	 */
	public function sql($intType = MONTY_QUERY_SELECT)
	{
		$this->_buildQuery($intType);
		return $this->_strQuery;
	}

	/**
	 * Monty_MySQL_Easy::update()
	 *
	 * @param array $arrFields
	 * @return bool $boolHasSucceeded
	 */
	public function update($arrFields)
	{
		$this->_arrFields = $arrFields;
		$this->_boolDirty = true;
		return $this->_buildQuery(MONTY_QUERY_UPDATE);
	}

	/**
	 * Monty_MySQL_Easy::where()
	 *
	 * @param string $strField
	 * @param string $strComparison
	 * @param mixed $mixValue
	 * @return void
	 */
	public function where($strField, $strComparison, $mixValue)
	{
		$this->_boolDirty = true;
		$this->_arrWheres[] = array($strField, $strComparison, $mixValue);
	}

	/**
	 * Monty_MySQL_Easy::_buildQuery()
	 *
	 * @param int $intType
	 * @return $boolHasSucceeded
	 * @access protected
	 */
	protected function _buildQuery($intType = MONTY_QUERY_SELECT)
	{
		if (!$this->_boolDirty) {
			return false;
		}
		switch ($intType) {
			case MONTY_QUERY_SELECT:
				$strQuery = 'SELECT * FROM `' . $this->_arrTable[0] . '` ' . $this->
					_arrTable[1];
				$strQuery .= $this->_buildQueryJoins();
				$strQuery .= $this->_buildQueryWheres();
				$strQuery .= $this->_buildQuerySorts();
				$strQuery .= $this->_buildQueryLimit();
				break;

			case MONTY_QUERY_INSERT:
				switch ($this->_intInsertType) {
					case MONTY_INSERT_NORMAL:
						$strQuery = 'INSERT INTO';
						break;
					case MONTY_INSERT_IGNORE:
						$strQuery = 'INSERT IGNORE INTO';
						break;
					case MONTY_INSERT_REPLACE:
						$strQuery = 'REPLACE INTO';
						break;
				}
				$strQuery .= ' `' . $this->_arrTable[0] . '`';
				$strQuery .= $this->_buildQueryFields();
				break;

			case MONTY_QUERY_UPDATE:
				$strQuery = 'UPDATE';
				$strQuery .= ' `' . $this->_arrTable[0] . '`';
				$strQuery .= $this->_buildQueryFields();
				$strQuery .= $this->_buildQueryWheres();
				$strQuery .= $this->_buildQuerySorts();
				$strQuery .= $this->_buildQueryLimit();
				break;
		}
		$this->_boolDirty = false;
		return $this->query($strQuery);
	}

	/**
	 * Monty_MySQL_Easy::_buildQueryFields()
	 *
	 * @return string $strFields
	 */
	protected function _buildQueryFields()
	{
		$strFields = ' SET';
		$i = 0;
		foreach ($this->_arrFields as $strField => $strValue) {
			$arrField = array($strField, $strValue);
			if (stristr($arrField[0], '.')) {
				$arrFieldName = explode('.', $arrField[0], 2);
				$strField = '`' . $arrFieldName[1] . '`';
			}
			else {
				$strField = '`' . $arrField[0] . '`';
			}
			$strFields .= ' ' . $strField . ' =';
			if (is_null($arrField[1])) {
				$strFields .= ' NULL';
			}
			else {
				$strFields .= ' "' . mysql_real_escape_string($arrField[1]) . '"';
			}
			if ($i + 1 < count($this->_arrFields)) {
				$strFields .= ',';
			}
			$i++;
		}
		return $strFields;
	}

	/**
	 * Monty_MySQL_Easy::_buildQueryJoins()
	 *
	 * @return string $strJoins
	 */
	protected function _buildQueryJoins()
	{
		$strJoins = '';
		foreach ($this->_arrJoins as $arrJoin) {
			switch ($arrJoin[2]) {
				case MONTY_JOIN_NORMAL:
					$strJoins .= ' JOIN';
					break;
				case MONTY_JOIN_LEFT:
					$strJoins .= ' LEFT JOIN';
					break;
				case MONTY_JOIN_RIGHT:
					$strJoins .= ' RIGHT JOIN';
					break;
			}
			$strJoins .= ' `' . $arrJoin[0] . '` ' . $arrJoin[1];
		}
		return $strJoins;
	}

	/**
	 * Monty_MySQL_Easy::_buildQueryLimit()
	 *
	 * @return string $strLimit
	 */
	protected function _buildQueryLimit()
	{
		$strLimit = '';
		if($this->_intLimitStart !== null) {
			$strLimit = ' LIMIT ' . $this->_intLimitStart . ', ' . $this->_intLimitCount;
		}
		return $strLimit;
	}

	/**
	 * Monty_MySQL_Easy::_buildQuerySorts()
	 *
	 * @return string $strSorts
	 */
	protected function _buildQuerySorts()
	{
		$strSorts = '';
		if (count($this->_arrSorts)) {
			$strSorts .= ' ORDER BY';
			for ($i = 0; $i < count($this->_arrSorts); $i++) {
				$arrSort = $this->_arrSorts[$i];
				if ($arrSort[0] !== null) {
					if (stristr($arrSort[0], '.')) {
						$arrField = explode('.', $arrSort[0], 2);
						$strField = $arrField[0] . '.`' . $arrField[1] . '`';
					}
					else {
						$strField = '`' . $arrSort[0] . '`';
					}
					$strSorts .= ' ' . $strField;
					if ($arrSort[1] < 0) {
						$strSorts .= ' DESC';
					}
					else {
						$strSorts .= ' ASC';
					}
				}
				else {
					$strSorts .= ' RAND()';
				}
				if ($i + 1 < count($this->_arrSorts)) {
					$strSorts .= ',';
				}
			}
		}
		return $strSorts;
	}

	/**
	 * Monty_MySQL_Easy::_buildQueryWheres()
	 *
	 * @return string $strWheres
	 */
	protected function _buildQueryWheres()
	{
		$strJoins = '';
		if (count($this->_arrWheres)) {
			$strJoins .= ' WHERE';
			for ($i = 0; $i < count($this->_arrWheres); $i++) {
				$arrWhere = $this->_arrWheres[$i];
				if (stristr($arrWhere[0], '.')) {
					$arrField = explode('.', $arrWhere[0], 2);
					$strField = $arrField[0] . '.`' . $arrField[1] . '`';
				}
				else {
					$strField = '`' . $arrWhere[0] . '`';
				}
				$strJoins .= ' ' . $strField . ' ' . $arrWhere[1];
				if (is_null($arrWhere[2])) {
					$strJoins .= ' NULL';
				}
				else {
					$strJoins .= ' "' . mysql_real_escape_string($arrWhere[2]) . '"';
				}
				if ($i + 1 < count($this->_arrWheres)) {
					$strJoins .= ' AND';
				}
			}
		}
		return $strJoins;
	}
}
