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
    protected static $_arrOperands;
    protected $_arrFields;
    protected $_arrJoins;
    protected $_arrSorts;
    protected $_arrTables;
    protected $_arrWheres;
    protected $_boolDirty;
    protected $_intInsertType;
    protected $_intLimitCount;
    protected $_intLimitStart;
    protected $_intWheres;

    /**
     * Monty_MySQL_Easy::__construct()
     *
     * @param string $strTable
     * @param string $strShortcut
     * @return \Monty_MySQL_Easy
     */
    public function __construct($strTable, $strShortcut = null)
    {
        parent::__construct();
        if (!$strShortcut) {
            $strShortcut = substr($strTable, 0, 1);
        }
        self::$_arrComparisons = array('eq' => '=', 'gt' => '>', 'gte' => '>=',
            'like' => 'LIKE', 'lt' => '<', 'lte' => '<=',
            'ne' => '!=', 'regexp' => 'REGEXP');
        self::$_arrOperands = array('and' => 'AND', 'or' => 'OR');
        $this->_arrJoins = array();
        $this->_arrTables = array(array($strTable, $strShortcut));
        $this->_arrWheres = array();
        $this->_boolDirty = true;
        $this->_intInsertType = null;
        $this->_intLimitCount = null;
        $this->_intLimitStart = null;
        $this->_intWheres = 0;
    }

    /**
     * Monty_MySQL_Easy::__call()
     *
     * @param string $strMethod
     * @param array $arrParams
     * @return string $mixReturn
     */
    public function __call($strMethod, $arrParams)
    {
        if (substr($strMethod, 0, 1) == '_') {
            trigger_error("$strMethod is not a public method.", E_USER_ERROR);
            return '';
        }
        if (in_array($strMethod, array_keys(self::$_arrComparisons))) {
            $boolValueIsField = isset($arrParams[2]) ? $arrParams[2] : false;
            return $this->where($arrParams[0],
                self::$_arrComparisons[$strMethod],
                $arrParams[1],
                $boolValueIsField);
        }
        if (in_array($strMethod, array_keys(self::$_arrOperands))) {
            if (count($arrParams) == 1 && is_array($arrParams[0]))
            {
                $arrParams = $arrParams[0];
            }
            return $this->_mergeWheres(self::$_arrOperands[$strMethod],
                $arrParams);
        }
        trigger_error("$strMethod is not a method.", E_USER_ERROR);
        return false;
    }

    /**
     * Monty_MySQL_Easy::add()
     *
     * @param string $strTable
     * @param string|null $strShortcut
     * @return void
     */
    public function add($strTable, $strShortcut = null)
    {
        $this->_boolDirty = true;
        $this->_arrTables[] = array($strTable, $strShortcut);
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
     * Monty_MySQL_Easy::contains()
     *
     * @param string $strField
     * @param string $strValue
     * @return string
     */
    public function contains($strField, $strValue)
    {
        return $this->where($strField, 'LIKE', '%' . $strValue . '%');
    }

    /**
     * Monty_MySQL_Easy::delete()
     *
     * @return bool $boolHasSucceeded
     */
    public function delete()
    {
        $this->_boolDirty = true;
        return $this->_buildQuery(MONTY_QUERY_DELETE);
    }

    /**
     * Monty_MySQL_Easy::fields()
     *
     * @param $arrFields
     * @return void
     */
    public function fields($arrFields)
    {
        if (is_array($arrFields)) {
            $this->_arrFields = $arrFields;
        }
        else
        {
            $this->_arrFields = func_get_args();
        }
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
     * @param string|null $strShortcut
     * @param int $intJoinType
     * @param $strOn1
     * @param $strOn2
     * @return void
     */
    public function join($strTable, $strShortcut = null, $intJoinType = MONTY_JOIN_NORMAL, $strOn1, $strOn2)
    {
        $this->_boolDirty = true;
        $this->_arrJoins[] = array($strTable, $strShortcut, $intJoinType, $strOn1, $strOn2);
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
        }
        elseif (func_num_args() == 2)
        {
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
        return parent::nextfield($mixField);
    }

    /**
     * Monty_MySQL_Easy::query()
     *
     * @param string $strQuery
     * @return bool $boolHasSucceeded
     */
    public function query($strQuery)
    {
        if (!parent::query($strQuery)) {
            trigger_error($this->error(), E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * Monty_MySQL_Easy::queryall()
     *
     * @param string $strQuery
     * @param int $intType
     * @return array
     */
    public function queryall($strQuery, $intType = MONTY_NEXT_ARRAY)
    {
        $this->query($strQuery);
        $this->_boolDirty = false;
        return $this->all($intType);
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
     * Monty_MySQL_Easy::replace()
     *
     * @param array $arrFields
     * @return bool $boolHasSucceeded
     */
    public function replace($arrFields)
    {
        return $this->insert($arrFields, MONTY_INSERT_REPLACE);
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
     * Monty_MySQL_Easy::starts()
     *
     * @param string $strField
     * @param string $strValue
     * @return string
     */
    public function starts($strField, $strValue)
    {
        return $this->where($strField, 'LIKE', $strValue . '%');
    }

    /**
     * Monty_MySQL_Easy::truncate()
     *
     * @return bool $boolHasSucceeded
     */
    public function truncate()
    {
        $this->_boolDirty = true;
        return $this->_buildQuery(MONTY_QUERY_TRUNCATE);
    }

    /**
     * Monty_MySQL_Easy::update()
     *
     * @param array|string $arrFields
     * @param string $strValue
     * @return bool $boolHasSucceeded
     */
    public function update($arrFields, $strValue = null)
    {
        if ($strValue !== null) {
            $arrFields = array($arrFields => $strValue);
        }
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
     * @param bool $boolValueIsField
     * @return string $strHash
     */
    public function where($strField, $strComparison, $mixValue, $boolValueIsField = false)
    {
        $this->_boolDirty = true;
        $strWhere = '';
        if (stristr($strField, '.')) {
            $arrField = explode('.', $strField, 2);
            $strField = $arrField[0] . '.`' . $arrField[1] . '`';
        }
        else
        {
            $strField = '`' . $strField . '`';
        }
        $strWhere .= ' ' . $strField;
        if (is_null($mixValue)) {
            if ($strComparison == '=')
                $strWhere .= ' IS';
            else {
                $strWhere .= ' IS NOT';
            }
            $strWhere .= ' NULL';
        }
        elseif ($boolValueIsField)
        {
            $arrField = explode('.', $mixValue, 2);
            $strWhere .=  ' ' . $strComparison . ' ' . $arrField[0] . '.`' . $arrField[1] . '`';
        }
        else
        {
            $strWhere .= ' ' . $strComparison . ' "' . mysql_real_escape_string($mixValue) . '"';
        }

        $strHash = $this->_intWheres++;
        $this->_arrWheres[$strHash] = $strWhere;
        return $strHash;
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
        $strQuery = '';
        switch ($intType)
        {
            case MONTY_QUERY_SELECT:
                $strQuery = 'SELECT ';
                if (count($this->_arrFields)) {
                    $strQuery .= $this->_buildQueryFields($intType);
                }
                else
                {
                    $strQuery .= '*';
                }
                $strQuery .= ' FROM';
                $i = 0;
                foreach ($this->_arrTables as $arrTable)
                {
                    $strQuery .= ' `' . $arrTable[0] . '` ' . $arrTable[1];
                    if ($i + 1 < count($this->_arrTables)) {
                        $strQuery .= ',';
                    }
                    $i++;
                }
                $strQuery .= $this->_buildQueryJoins();
                $strQuery .= $this->_buildQueryWheres();
                $strQuery .= $this->_buildQuerySorts();
                $strQuery .= $this->_buildQueryLimit($intType);
                break;

            case MONTY_QUERY_INSERT:
                switch ($this->_intInsertType)
                {
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
                $strQuery .= ' `' . $this->_arrTables[0][0] . '`';
                $strQuery .= $this->_buildQueryFields($intType);
                break;

            case MONTY_QUERY_UPDATE:
                $strQuery = 'UPDATE';
                $strQuery .= ' `' . $this->_arrTables[0][0] . '`';
                $strQuery .= $this->_buildQueryFields($intType);
                $strQuery .= $this->_buildQueryWheres();
                $strQuery .= $this->_buildQuerySorts();
                $strQuery .= $this->_buildQueryLimit($intType);
                break;

            case MONTY_QUERY_DELETE:
                $strQuery = 'DELETE FROM';
                $strQuery .= ' `' . $this->_arrTables[0][0] . '`';
                $strQuery .= $this->_buildQueryWheres();
                $strQuery .= $this->_buildQuerySorts();
                $strQuery .= $this->_buildQueryLimit($intType);
                break;

            case MONTY_QUERY_TRUNCATE:
                $strQuery = 'TRUNCATE';
                $strQuery .= ' `' . $this->_arrTables[0][0] . '`';
                break;
        }
        $this->_boolDirty = false;
        return $this->query($strQuery);
    }

    /**
     * Monty_MySQL_Easy::_buildQueryFields()
     *
     * @param int $intType
     * @return string $strFields
     */
    protected function _buildQueryFields($intType)
    {
        $strFields = '';
        switch ($intType)
        {
            case MONTY_QUERY_SELECT:
                $i = 0;
                foreach ($this->_arrFields as $strField)
                {
                    if (stristr($strField, '.')) {
                        $arrFieldName = explode('.', $strField, 2);
                        $strField = $arrFieldName[0] . '.`' . $arrFieldName[1] . '`';
                    }
                    else
                    {
                        $strField = '`' . $strField . '`';
                    }
                    $strFields .= ' ' . $strField;
                    if ($i + 1 < count($this->_arrFields)) {
                        $strFields .= ',';
                    }
                    $i++;
                }
                break;

            case MONTY_QUERY_INSERT:
            case MONTY_QUERY_UPDATE:
            case MONTY_QUERY_DELETE:
                $strFields = ' SET';
                $i = 0;
                foreach ($this->_arrFields as $strField => $strValue)
                {
                    $arrField = array($strField, $strValue);
                    if (stristr($arrField[0], '.')) {
                        $arrFieldName = explode('.', $arrField[0], 2);
                        $strField = '`' . $arrFieldName[1] . '`';
                    }
                    else
                    {
                        $strField = '`' . $arrField[0] . '`';
                    }
                    $strFields .= ' ' . $strField . ' =';
                    if (is_null($arrField[1])) {
                        $strFields .= ' NULL';
                    }
                    else
                    {
                        $strFields .= ' "' . mysql_real_escape_string($arrField[1]) . '"';
                    }
                    if ($i + 1 < count($this->_arrFields)) {
                        $strFields .= ',';
                    }
                    $i++;
                }
                break;
        }
        return $strFields;
    }

    /**
     * Monty_MySQL_Easy::_buildQueryLimit()
     *
     * @param int $intType
     * @return string $strLimit
     */
    protected function _buildQueryLimit($intType)
    {
        $strLimit = '';
        if ($this->_intLimitStart !== null) {
            $strLimit = ' LIMIT ' . $this->_intLimitStart . ', ' . $this->_intLimitCount;
        }
        if ($intType != MONTY_QUERY_SELECT && $this->_intLimitCount !== null) {
            $strLimit = ' LIMIT ' . $this->_intLimitCount;
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
            for ($i = 0; $i < count($this->_arrSorts); $i++)
            {
                $arrSort = $this->_arrSorts[$i];
                if ($arrSort[0] !== null) {
                    if (stristr($arrSort[0], '.')) {
                        $arrField = explode('.', $arrSort[0], 2);
                        $strField = $arrField[0] . '.`' . $arrField[1] . '`';
                    }
                    else
                    {
                        $strField = '`' . $arrSort[0] . '`';
                    }
                    $strSorts .= ' ' . $strField;
                    if ($arrSort[1] < 0) {
                        $strSorts .= ' DESC';
                    }
                    else
                    {
                        $strSorts .= ' ASC';
                    }
                }
                else
                {
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
     * Monty_MySQL_Easy::_buildQueryJoins()
     *
     * @return string $strJoins
     */
    protected function _buildQueryJoins()
    {
        $strJoins = '';
        if (count($this->_arrJoins)) {
            foreach ($this->_arrJoins as $arrJoin)
            {
                $intJoinType = $arrJoin[2];
                switch ($intJoinType)
                {
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
                $strJoins .= ' ON (';
                $arrOn1 = explode('.', $arrJoin[3]);
                $arrOn2 = explode('.', $arrJoin[4]);
                $strJoins .= ' ' . $arrOn1[0] . '.`' . $arrOn1[1] . '`';
                $strJoins .= ' = ' . $arrOn2[0] . '.`' . $arrOn2[1] . '`)';
            }
        }
        return $strJoins;
    }

    /**
     * Monty_MySQL_Easy::_buildQueryWheres()
     *
     * @return string $strWheres
     */
    protected function _buildQueryWheres()
    {
        $strWheres = '';
        if (count($this->_arrWheres)) {
            $strHash = $this->_mergeWheres('AND', array_keys($this->_arrWheres));
            $strWheres .= ' WHERE';
            $strWheres .= $this->_arrWheres[$strHash];
        }
        return $strWheres;
    }

    /**
     * Monty_MySQL_Easy::_mergeWheres()
     *
     * @param $strOperand
     * @param $arrWheres
     * @return string
     */
    protected function _mergeWheres($strOperand, $arrWheres)
    {
        if (!count($arrWheres)) {
            return '';
        }
        elseif (count($arrWheres) == 1)
        {
            $arrWheres = array_values($arrWheres);
            return $arrWheres[0];
        }

        $strWheres = ' (';
        $i = 0;
        foreach ($arrWheres as $strHash)
        {
            $strWheres .= $this->_arrWheres[$strHash];
            unset($this->_arrWheres[$strHash]);
            if ($i + 1 < count($arrWheres)) {
                $strWheres .= ' ' . $strOperand;
            }
            $i++;
        }

        $strWheres .= ' )';

        $strHash = $this->_intWheres++;
        $this->_arrWheres[$strHash] = $strWheres;

        return $strHash;
    }
}
