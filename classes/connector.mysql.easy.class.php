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
 * @link      https://github.com/mynetx/monty/
 */

/**
 * Monty_MySQL_Easy
 *
 * @category   Database
 * @package    Monty
 * @author     J.M. <me@mynetx.net>
 * @copyright  2011 J.M. <me@mynetx.net>
 * @license    http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link       https://github.com/mynetx/monty/
 * @deprecated since 2.2.0
 */
class Monty_MySQL_Easy extends Monty_MySQL
{
    protected static $arrComparisons;
    protected static $arrOperands;
    protected $arrFields;
    protected $arrJoins;
    protected $arrSorts;
    protected $arrTables;
    protected $arrWheres;
    protected $boolDirty;
    protected $intInsertType;
    protected $intLimitCount;
    protected $intLimitStart;
    protected $intWheres;

    /**
     * Monty_MySQL_Easy::__construct()
     *
     * @param string $strTable    The table name to work with
     * @param string $strShortcut Optional table shortcut letter
     *                            for multi-table operations
     *
     * @return \Monty_MySQL_Easy
     */
    public function __construct($strTable, $strShortcut = null)
    {
        parent::__construct();
        if (! $strShortcut) {
            $strShortcut = substr($strTable, 0, 1);
        }
        self::$arrComparisons = array(
            'eq' => '=',
            'gt' => '>',
            'gte' => '>=',
            'like' => 'LIKE',
            'lt' => '<',
            'lte' => '<=',
            'ne' => '!=',
            'regexp' => 'REGEXP'
        );
        self::$arrOperands = array(
            'and' => 'AND',
            'or' => 'OR'
        );
        $this->arrJoins = array();
        $this->arrTables = array(array($strTable, $strShortcut));
        $this->arrWheres = array();
        $this->boolDirty = true;
        $this->intInsertType = null;
        $this->intLimitCount = null;
        $this->intLimitStart = null;
        $this->intWheres = 0;
    }

    /**
     * Monty_MySQL_Easy::__call()
     *
     * @param string $strMethod The magic method called
     * @param array  $arrParams All sent parameters
     *
     * @return string $mixReturn
     */
    public function __call($strMethod, $arrParams)
    {
        if (substr($strMethod, 0, 1) === '_') {
            trigger_error("$strMethod is not a public method.", E_USER_ERROR);
            return '';
        }
        if (in_array($strMethod, array_keys(self::$arrComparisons))) {
            $boolValueIsField
                = isset($arrParams[2])
                ? $arrParams[2]
                : false;
            return $this->where(
                $arrParams[0],
                self::$arrComparisons[$strMethod],
                $arrParams[1],
                $boolValueIsField
            );
        }
        if (in_array($strMethod, array_keys(self::$arrOperands))) {
            if (count($arrParams) == 1 && is_array($arrParams[0])) {
                $arrParams = $arrParams[0];
            }
            return $this->mergeWheres(
                self::$arrOperands[$strMethod],
                $arrParams
            );
        }
        trigger_error("$strMethod is not a method.", E_USER_ERROR);
        return false;
    }

    /**
     * Monty_MySQL_Easy::add()
     *
     * @param string      $strTable    The name of the additional table
     * @param string|null $strShortcut Optional shortcut letter for this table
     *
     * @return void
     */
    public function add($strTable, $strShortcut = null)
    {
        $this->boolDirty = true;
        $this->arrTables[] = array($strTable, $strShortcut);
    }

    /**
     * Monty_MySQL_Easy::all()
     *
     * @param int $intType The return type
     *
     * @return array $arrRows
     */
    public function all($intType = null)
    {
        $this->buildQuery();
        return parent::all($intType);
    }

    /**
     * Monty_MySQL_Easy::contains()
     *
     * @param string $strField The column name to look into
     * @param string $strValue The value to check for
     *
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
        $this->boolDirty = true;
        return $this->buildQuery(MONTY_QUERY_DELETE);
    }

    /**
     * Monty_MySQL_Easy::fields()
     *
     * @param array $arrFields The fields to return from this query
     *
     * @return void
     */
    public function fields($arrFields = array())
    {
        if (is_array($arrFields)) {
            $this->arrFields = $arrFields;
        } else {
            $this->arrFields = func_get_args();
        }
    }

    /**
     * Monty_MySQL_Easy::insert()
     *
     * @param array $arrFields The array or fields to insert the row with
     * @param int   $intType   Whether to insert, insert ignore or replace into
     *
     * @return bool $boolHasSucceeded
     */
    public function insert($arrFields, $intType = MONTY_INSERT_NORMAL)
    {
        $this->arrFields = $arrFields;
        $this->boolDirty = true;
        $this->intInsertType = $intType;
        return $this->buildQuery(MONTY_QUERY_INSERT);
    }

    /**
     * Monty_MySQL_Easy::join()
     *
     * @param string      $strTable    The table to join with
     * @param string|null $strShortcut Optional table shortcut
     * @param int         $intJoinType Normal join, left or right join
     * @param string      $strOn1      Field of the first linked table
     * @param string      $strOn2      Corresponding field of the second linked table
     *
     * @return void
     */
    public function join(
        $strTable,
        $strShortcut,
        $intJoinType,
        $strOn1,
        $strOn2
    ) {
        $this->boolDirty = true;
        $this->arrJoins[] = array(
            $strTable, 
            $strShortcut, 
            $intJoinType, 
            $strOn1, 
            $strOn2
        );
    }

    /**
     * Monty_MySQL_Easy::limit()
     *
     * @ param int $intStart Left end of the interval
     * @ param int $intCount Length of the interval
     *
     * @return void
     */
    public function limit()
    {
        $this->boolDirty = true;
        if (func_num_args() == 1) {
            $this->intLimitCount = func_get_arg(0);
            $this->intLimitStart = 0;
        } elseif (func_num_args() == 2) {
            $this->intLimitCount = func_get_arg(1);
            $this->intLimitStart = func_get_arg(0);
        }
    }

    /**
     * Monty_MySQL_Easy::next()
     *
     * @param int $intType The return type
     *
     * @return mixed $mixRow
     */
    public function next($intType = null)
    {
        $this->buildQuery();
        return parent::next($intType);
    }

    /**
     * Monty_MySQL_Easy::nextfield()
     *
     * @param mixed $mixField Optional field index
     *
     * @return mixed $mixField
     */
    public function nextfield($mixField = 0)
    {
        $this->buildQuery();
        return parent::nextfield($mixField);
    }

    /**
     * Monty_MySQL_Easy::query()
     *
     * @param string $strQuery The SQL query to execute
     *
     * @return bool $boolHasSucceeded
     */
    public function query($strQuery)
    {
        if (! parent::query($strQuery)) {
            trigger_error($this->error(), E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * Monty_MySQL_Easy::queryall()
     *
     * @param string $strQuery The SQL query to execute
     * @param int    $intType  The return type
     *
     * @return array
     */
    public function queryall($strQuery, $intType = null)
    {
        $this->query($strQuery);
        $this->boolDirty = false;
        return $this->all($intType);
    }

    /**
     * Monty_MySQL_Easy::rand()
     *
     * @return void
     */
    public function rand()
    {
        $this->boolDirty = true;
        $this->arrSorts = array(array(null, 1));
    }

    /**
     * Monty_MySQL_Easy::replace()
     *
     * @param array $arrFields The array of fields to replace the rows with
     *
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
        $this->buildQuery();
        return parent::rows();
    }

    /**
     * Monty_MySQL_Easy::seek()
     *
     * @param int $intRow The row number to set the pointer to
     *
     * @return bool $boolHasSucceeded
     */
    public function seek($intRow)
    {
        $this->buildQuery();
        return parent::seek($intRow);
    }

    /**
     * Monty_MySQL_Easy::sort()
     *
     * @param string $strBy  Column to sort the result by
     * @param int    $intAsc Whether to sort ascending or descending
     *
     * @return void
     */
    public function sort($strBy, $intAsc = 1)
    {
        $this->boolDirty = true;
        $this->arrSorts[] = array($strBy, $intAsc);
    }

    /**
     * Monty_MySQL_Easy::sql()
     *
     * @param int $intType The query type to generate
     *
     * @return string $strQuery
     */
    public function sql($intType = MONTY_QUERY_SELECT)
    {
        $this->buildQuery($intType);
        return $this->strQuery;
    }

    /**
     * Monty_MySQL_Easy::starts()
     *
     * @param string $strField The field to compare with
     * @param string $strValue The value to check for
     *
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
        $this->boolDirty = true;
        return $this->buildQuery(MONTY_QUERY_TRUNCATE);
    }

    /**
     * Monty_MySQL_Easy::update()
     *
     * @param array|string $arrFields The field(s) to update
     * @param string       $strValue  The data to insert into
     *
     * @return bool $boolHasSucceeded
     */
    public function update($arrFields, $strValue = null)
    {
        if ($strValue !== null) {
            $arrFields = array($arrFields => $strValue);
        }
        $this->arrFields = $arrFields;
        $this->boolDirty = true;
        return $this->buildQuery(MONTY_QUERY_UPDATE);
    }

    /**
     * Monty_MySQL_Easy::where()
     *
     * @param string $strField         Field to compare with
     * @param string $strComparison    Comparison operator
     * @param mixed  $mixValue         Data or field name to check for
     * @param bool   $boolValueIsField Whether $mixValue is a field name
     *
     * @return string $strHash
     */
    public function where(
        $strField,
        $strComparison,
        $mixValue,
        $boolValueIsField = false
    ) {
        $this->boolDirty = true;
        $strWhere = '';
        if (stristr($strField, '.')) {
            $arrField = explode('.', $strField, 2);
            $strField = $arrField[0] . '.`' . $arrField[1] . '`';
        } else {
            $strField = '`' . $strField . '`';
        }
        $strWhere .= ' ' . $strField;
        if (is_null($mixValue)) {
            if ($strComparison == '=') {
                $strWhere .= ' IS';
            } else {
                $strWhere .= ' IS NOT';
            }
            $strWhere .= ' NULL';
        } elseif ($boolValueIsField) {
            $arrField = explode('.', $mixValue, 2);
            $strWhere
                .=  ' ' . $strComparison
                . ' ' . $arrField[0]
                . '.`' . $arrField[1] . '`';
        } else {
            $strWhere
                .= ' ' . $strComparison
                . ' "' . mysql_real_escape_string($mixValue) . '"';
        }

        $strHash = $this->intWheres++;
        $this->arrWheres[$strHash] = $strWhere;
        return $strHash;
    }

    /**
     * Monty_MySQL_Easy::buildQuery()
     *
     * @param int $intType The query type to generate
     *
     * @return $boolHasSucceeded
     * @access protected
     */
    protected function buildQuery($intType = MONTY_QUERY_SELECT)
    {
        if (!$this->boolDirty) {
            return false;
        }
        $strQuery = '';
        switch ($intType) {
        case MONTY_QUERY_SELECT:
            $strQuery = 'SELECT ';
            if (count($this->arrFields)) {
                $strQuery .= $this->buildQueryFields($intType);
            } else {
                $strQuery .= '*';
            }
            $strQuery .= ' FROM';
            $i = 0;
            foreach ($this->arrTables as $arrTable) {
                $strQuery .= ' `' . $arrTable[0] . '` ' . $arrTable[1];
                if ($i + 1 < count($this->arrTables)) {
                    $strQuery .= ',';
                }
                $i++;
            }
            $strQuery .= $this->buildQueryJoins();
            $strQuery .= $this->buildQueryWheres();
            $strQuery .= $this->buildQuerySorts();
            $strQuery .= $this->buildQueryLimit($intType);
            break;

        case MONTY_QUERY_INSERT:
            switch ($this->intInsertType) {
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
            $strQuery .= ' `' . $this->arrTables[0][0] . '`';
            $strQuery .= $this->buildQueryFields($intType);
            break;

        case MONTY_QUERY_UPDATE:
            $strQuery = 'UPDATE';
            $strQuery .= ' `' . $this->arrTables[0][0] . '`';
            $strQuery .= $this->buildQueryFields($intType);
            $strQuery .= $this->buildQueryWheres();
            $strQuery .= $this->buildQuerySorts();
            $strQuery .= $this->buildQueryLimit($intType);
            break;

        case MONTY_QUERY_DELETE:
            $strQuery = 'DELETE FROM';
            $strQuery .= ' `' . $this->arrTables[0][0] . '`';
            $strQuery .= $this->buildQueryWheres();
            $strQuery .= $this->buildQuerySorts();
            $strQuery .= $this->buildQueryLimit($intType);
            break;

        case MONTY_QUERY_TRUNCATE:
            $strQuery = 'TRUNCATE';
            $strQuery .= ' `' . $this->arrTables[0][0] . '`';
            break;
        }
        $this->boolDirty = false;
        return $this->query($strQuery);
    }

    /**
     * Monty_MySQL_Easy::buildQueryFields()
     *
     * @param int $intType The query type to generate
     *
     * @return string $strFields
     */
    protected function buildQueryFields($intType)
    {
        $strFields = '';
        switch ($intType) {
        case MONTY_QUERY_SELECT:
            $i = 0;
            foreach ($this->arrFields as $strField) {
                if (stristr($strField, '.')) {
                    $arrFieldName = explode('.', $strField, 2);
                    $strField = $arrFieldName[0] . '.`' . $arrFieldName[1] . '`';
                } else {
                    $strField = '`' . $strField . '`';
                }
                $strFields .= ' ' . $strField;
                if ($i + 1 < count($this->arrFields)) {
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
            foreach ($this->arrFields as $strField => $strValue) {
                $arrField = array($strField, $strValue);
                if (stristr($arrField[0], '.')) {
                    $arrFieldName = explode('.', $arrField[0], 2);
                    $strField = '`' . $arrFieldName[1] . '`';
                } else {
                    $strField = '`' . $arrField[0] . '`';
                }
                $strFields .= ' ' . $strField . ' =';
                if (is_null($arrField[1])) {
                    $strFields .= ' NULL';
                } else {
                    $strFields
                        .= ' "'
                        . mysql_real_escape_string($arrField[1])
                        . '"';
                }
                if ($i + 1 < count($this->arrFields)) {
                    $strFields .= ',';
                }
                $i++;
            }
            break;
        }
        return $strFields;
    }

    /**
     * Monty_MySQL_Easy::buildQueryLimit()
     *
     * @param int $intType Type of the query to generate
     *
     * @return string $strLimit
     */
    protected function buildQueryLimit($intType)
    {
        $strLimit = '';
        if ($this->intLimitStart !== null) {
            $strLimit
                = ' LIMIT ' . $this->intLimitStart . ', '
                . $this->intLimitCount;
        }
        if ($intType != MONTY_QUERY_SELECT && $this->intLimitCount !== null) {
            $strLimit = ' LIMIT ' . $this->intLimitCount;
        }
        return $strLimit;
    }

    /**
     * Monty_MySQL_Easy::buildQuerySorts()
     *
     * @return string $strSorts
     */
    protected function buildQuerySorts()
    {
        $strSorts = '';
        if (count($this->arrSorts)) {
            $strSorts .= ' ORDER BY';
            for ($i = 0; $i < count($this->arrSorts); $i++) {
                $arrSort = $this->arrSorts[$i];
                if ($arrSort[0] !== null) {
                    if (stristr($arrSort[0], '.')) {
                        $arrField = explode('.', $arrSort[0], 2);
                        $strField = $arrField[0] . '.`' . $arrField[1] . '`';
                    } else {
                        $strField = '`' . $arrSort[0] . '`';
                    }
                    $strSorts .= ' ' . $strField;
                    if ($arrSort[1] < 0) {
                        $strSorts .= ' DESC';
                    } else {
                        $strSorts .= ' ASC';
                    }
                } else {
                    $strSorts .= ' RAND()';
                }
                if ($i + 1 < count($this->arrSorts)) {
                    $strSorts .= ',';
                }
            }
        }
        return $strSorts;
    }

    /**
     * Monty_MySQL_Easy::buildQueryJoins()
     *
     * @return string $strJoins
     */
    protected function buildQueryJoins()
    {
        $strJoins = '';
        if (count($this->arrJoins)) {
            foreach ($this->arrJoins as $arrJoin) {
                $intJoinType = $arrJoin[2];
                switch ($intJoinType) {
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
     * Monty_MySQL_Easy::buildQueryWheres()
     *
     * @return string $strWheres
     */
    protected function buildQueryWheres()
    {
        $strWheres = '';
        if (count($this->arrWheres)) {
            $strHash = $this->mergeWheres('AND', array_keys($this->arrWheres));
            $strWheres .= ' WHERE';
            $strWheres .= $this->arrWheres[$strHash];
        }
        return $strWheres;
    }

    /**
     * Monty_MySQL_Easy::mergeWheres()
     *
     * @param string $strOperand Boolean operator to use for merging conditions
     * @param array  $arrWheres  Array of conditions
     *
     * @return string
     */
    protected function mergeWheres($strOperand, $arrWheres)
    {
        if (! count($arrWheres)) {
            return '';
        } elseif (count($arrWheres) == 1) {
            $arrWheres = array_values($arrWheres);
            return $arrWheres[0];
        }

        $strWheres = ' (';
        $i = 0;
        foreach ($arrWheres as $strHash) {
            $strWheres .= $this->arrWheres[$strHash];
            unset($this->arrWheres[$strHash]);
            if ($i + 1 < count($arrWheres)) {
                $strWheres .= ' ' . $strOperand;
            }
            $i++;
        }

        $strWheres .= ' )';

        $strHash = $this->intWheres++;
        $this->arrWheres[$strHash] = $strWheres;

        return $strHash;
    }
}
