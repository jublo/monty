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
 * Monty_MySQL
 *
 * @category   Database
 * @package    Monty
 * @author     J.M. <me@mynetx.net>
 * @copyright  2011 J.M. <me@mynetx.net>
 * @license    http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link       https://github.com/mynetx/monty/
 * @deprecated since 2.2.0
 */
class Monty_MySQL extends Monty_Connector
{
    /**
     * Monty_MySQL::__construct()
     *
     * @return \Monty_MySQL
     */
    public function __construct()
    {
        $this->intRows = 0;
        $this->resQuery = null;
        $this->strQuery = null;
        $this->intReturnType = MONTY_ALL_ARRAY;
    }

    /**
     * Monty_MySQL::all()
     *
     * @param int $intType The return type
     *
     * @return array $arrRows
     */
    public function all($intType = null)
    {
        if (! $this->resQuery) {
            return false;
        }
        $arrRows = array();
        while ($arrRow = $this->next($intType)) {
            $arrRows[] = $arrRow;
        }
        return $arrRows;
    }


    /**
     * Monty_MySQL::error()
     *
     * @param int $intType The return type
     *
     * @return mixed $mixError
     */
    public function error($intType = MONTY_ERROR_STRING)
    {
        switch ($intType) {
        case MONTY_ERROR_STRING:
            return mysql_error();
        case MONTY_ERROR_ARRAY:
            return array(
                'text' => mysql_error(),
                'code' => mysql_errno()
            );
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
        if (! $this->strQuery) {
            return false;
        }
        return mysql_insert_id();
    }

    /**
     * Monty_MySQL::next()
     *
     * @param int $intType The return type
     *
     * @return mixed $mixRow
     */
    public function next($intType = null)
    {
        if (! $this->resQuery) {
            return false;
        }
        if ($intType === null) {
            $intType = $this->intReturnType;
        }
        switch ($intType) {
        case MONTY_NEXT_ARRAY:
            return mysql_fetch_assoc($this->resQuery);
        case MONTY_NEXT_OBJECT:
            return mysql_fetch_object($this->resQuery);
        }
    }


    /**
     * Monty_MySQL::nextfield()
     *
     * @param mixed $mixField Optional result column name/index
     *
     * @return mixed $mixField
     */
    public function nextfield($mixField = 0)
    {
        if (! $this->resQuery) {
            return false;
        }
        if (is_int($mixField)) {
            if (! $arrRow = mysql_fetch_row($this->resQuery)) {
                return false;
            }
            return isset($arrRow[$mixField]) ? $arrRow[$mixField] : false;
        }
        if (is_string($mixField)) {
            if (!$arrRow = mysql_fetch_assoc($this->resQuery)) {
                return false;
            }
            return isset($arrRow[$mixField]) ? $arrRow[$mixField] : false;
        }
    }

    /**
     * Monty_MySQL::open()
     *
     * @param string $strUser     The database user name
     * @param string $strPassword The database password
     * @param string $strDatabase Name of the database to connect to
     * @param string $strHost     Host name of database server
     * @param int    $intOpenType Whether to open a persistent connection
     *
     * @return bool $boolIsOpened
     */
    public function open(
        $strUser,
        $strPassword,
        $strDatabase,
        $strHost = 'localhost',
        $intOpenType = MONTY_OPEN_NORMAL
    ) {
        $strOpenFunction = '';
        switch ($intOpenType) {
        case MONTY_OPEN_NORMAL:
            $strOpenFunction = 'mysql_connect';
            break;
        case MONTY_OPEN_PERSISTENT:
            $strOpenFunction = 'mysql_pconnect';
            break;
        }
        if (! @$strOpenFunction($strHost, $strUser, $strPassword)) {
            return false;
        }
        if (! @mysql_select_db($strDatabase)) {
            return false;
        }
        @mysql_set_charset('utf8');
        return true;
    }

    /**
     * Monty_MySQL::query()
     *
     * @param string $strQuery The SQL query to execute
     *
     * @return bool $boolHasSucceeded
     */
    public function query($strQuery)
    {
        $this->resQuery = null;
        $this->strQuery = $strQuery;
        if (! $resQuery = @mysql_query($strQuery)) {
            return false;
        }
        $this->resQuery = $resQuery;
        $this->intRows = @mysql_num_rows($resQuery);
        return true;
    }

    /**
     * Monty_MySQL::rows()
     *
     * @return int $intRows
     */
    public function rows()
    {
        if (! $this->resQuery) {
            return false;
        }
        return $this->intRows;
    }

    /**
     * Monty_MySQL::seek()
     *
     * @param int $intRow The row to set the result pointer to
     *
     * @return bool $boolHasSucceeded
     */
    public function seek($intRow)
    {
        if (!$this->resQuery) {
            return false;
        }
        return mysql_data_seek($this->resQuery, $intRow);
    }

    /**
     * Monty_MySQL::setReturnType()
     * 
     * @param int $intReturnType The return type to set as default
     *
     * @return void
     */
    public function setReturnType($intReturnType)
    {
        $this->intReturnType = $intReturnType;
    }

    /**
     * Monty_MySQL::table()
     *
     * @param string $strTable    The table name
     * @param string $strShortcut Optional table shortcut character
     *
     * @return object Monty_MySQL_Easy
     */
    public function table($strTable, $strShortcut = null)
    {
        $easy = new Monty_MySQL_Easy($strTable, $strShortcut);
        $easy->setReturnType($this->intReturnType);
        return $easy;
    }

    /**
     * Monty_MySQL::tableExists()
     *
     * @param string $strTable The table to check for existence
     *
     * @return bool $boolExists
     */
    public function tableExists($strTable)
    {
        $this->query(
            'SHOW TABLES LIKE "' . mysql_real_escape_string($strTable) . '"'
        );
        return $this->rows() > 0;
    }
}
