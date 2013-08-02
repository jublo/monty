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
 * Monty_MySQLI
 *
 * @category  Database
 * @package   Monty
 * @author    J.M. <me@mynetx.net>
 * @copyright 2011 J.M. <me@mynetx.net>
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser Public License 3.0
 * @link      https://github.com/mynetx/monty/
 */
class Monty_MySQLI extends Monty_Connector
{
    protected $DB;

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
        $this->_intReturnType = MONTY_ALL_ARRAY;
        $this->DB = null;
    }

    /**
     * Monty_MySQLI::all()
     *
     * @param int $intType The return type
     *
     * @return array $arrRows
     */
    public function all($intType = null)
    {
        if (! $this->_resQuery) {
            return false;
        }
        $arrRows = array();
        while ($arrRow = $this->next($intType)) {
            $arrRows[] = $arrRow;
        }
        return $arrRows;
    }


    /**
     * Monty_MySQLI::error()
     *
     * @param int $intType The return type
     *
     * @return mixed $mixError
     */
    public function error($intType = MONTY_ERROR_STRING)
    {
        switch ($intType) {
        case MONTY_ERROR_STRING:
            return $this->DB->error;
        case MONTY_ERROR_ARRAY:
            return array(
                'text' => $this->DB->error,
                'code' => $this->DB->errno
            );
        case MONTY_ERROR_OBJECT:
            $objError = new stdClass;
            $objError->text = $this->DB->error;
            $objError->code = $this->DB->errno;
            return $objError;
        case MONTY_ERROR_NUMERIC:
            return $this->DB->errno;
        }
    }

    /**
     * Monty_MySQLI::id()
     *
     * @return int $intInsertId
     */
    public function id()
    {
        if (! $this->_strQuery) {
            return false;
        }
        return $this->DB->insert_id;
    }

    /**
     * Monty_MySQLI::next()
     *
     * @param int $intType The return type
     *
     * @return mixed $mixRow
     */
    public function next($intType = null)
    {
        if (! $this->_resQuery) {
            return false;
        }
        if ($intType === null) {
            $intType = $this->_intReturnType;
        }
        switch ($intType) {
        case MONTY_NEXT_ARRAY:
            return $this->_resQuery->fetch_assoc();
        case MONTY_NEXT_OBJECT:
            return $this->_resQuery->fetch_object();
        }
    }


    /**
     * Monty_MySQLI::nextfield()
     *
     * @param mixed $mixField Field index to return
     *
     * @return mixed $mixField
     */
    public function nextfield($mixField = 0)
    {
        if (! $this->_resQuery) {
            return false;
        }
        if (is_int($mixField)) {
            if (! $arrRow = $this->_resQuery->fetch_row()) {
                return false;
            }
            return isset($arrRow[$mixField]) ? $arrRow[$mixField] : false;
        }
        if (is_string($mixField)) {
            if (! $arrRow = $this->_resQuery->fetch_assoc()) {
                return false;
            }
            return isset($arrRow[$mixField]) ? $arrRow[$mixField] : false;
        }
    }

    /**
     * Monty_MySQLI::open()
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
        $strHostString = '';
        switch ($intOpenType) {
        case MONTY_OPEN_NORMAL:
            $strHostString = $strHost;
            break;
        case MONTY_OPEN_PERSISTENT:
            // persistent mysqli connections only for PHP 5.3+
            if (version_compare(phpversion(), '5.3.0', '>=')) {
                $strHostString = 'p:' . $strHost;
            } else {
                $strHostString = $strHost;
            }
            break;
        }
        if (!$this->DB = @new mysqli(
            $strHostString,
            $strUser,
            $strPassword,
            $strDatabase
        )) {
            return false;
        }
        if ($this->DB->connect_error) {
            return false;
        }
        $this->DB->set_charset('utf8');
        return true;
    }

    /**
     * Monty_MySQLI::query()
     *
     * @param string $strQuery The query to execute
     *
     * @return bool $boolHasSucceeded
     */
    public function query($strQuery)
    {
        $this->_resQuery = null;
        $this->_strQuery = $strQuery;
        if (! $resQuery = @$this->DB->query($strQuery)) {
            return false;
        }
        if ($resQuery === true) {
            return true;
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
        if (! $this->_resQuery) {
            return false;
        }
        return $this->_intRows;
    }

    /**
     * Monty_MySQLI::seek()
     *
     * @param int $intRow The row number to set the pointer to
     *
     * @return bool $boolHasSucceeded
     */
    public function seek($intRow)
    {
        if (! $this->_resQuery) {
            return false;
        }
        return $this->_resQuery->data_seek($intRow);
    }

    /** Monty_MySQLI::setReturnType()
     * 
     * @param int $intReturnType The return type to set as default
     *
     * @return void
     */
    public function setReturnType($intReturnType)
    {
        $this->_intReturnType = $intReturnType;
    }

    /**
     * Monty_MySQLI::table()
     *
     * @param string $strTable    The table to link to
     * @param string $strShortcut Optional table shortcut letter
     *
     * @return object Monty_MySQLI_Easy
     */
    public function table($strTable, $strShortcut = null)
    {
        $easy = new Monty_MySQLI_Easy($strTable, $strShortcut, $this->DB);
        $easy->setReturnType($this->_intReturnType);
        return $easy;
    }

    /**
     * Monty_MySQLI::tableExists()
     *
     * @param string $strTable The table to check for
     *
     * @return bool $boolExists
     */
    public function tableExists($strTable)
    {
        $this->query(
            'SHOW TABLES LIKE "' . $this->DB->real_escape_string($strTable) . '"'
        );
        return $this->rows() > 0;
    }
}
