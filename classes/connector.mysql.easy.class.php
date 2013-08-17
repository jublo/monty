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
    protected static $comparisons;
    protected static $operators;
    protected $fields_list;
    protected $joins;
    protected $sorts;
    protected $tables_list;
    protected $wheres;
    protected $is_dirty;
    protected $insert_type;
    protected $limit_count;
    protected $limit_start;
    protected $wheres_count;

    /**
     * Monty_MySQL_Easy::__construct()
     *
     * @param string $table_name     The table name to work with
     * @param string $table_shortcut Optional table shortcut letter
     *                               for multi-table operations
     *
     * @return \Monty_MySQL_Easy
     */
    public function __construct($table_name, $table_shortcut = null)
    {
        parent::__construct();
        if (!$table_shortcut) {
            $table_shortcut = substr($table_name, 0, 1);
        }
        self::$comparisons  = array(
            'eq'     => '=',
            'gt'     => '>',
            'gte'    => '>=',
            'like'   => 'LIKE',
            'lt'     => '<',
            'lte'    => '<=',
            'ne'     => '!=',
            'regexp' => 'REGEXP'
        );
        self::$operators    = array(
            'and' => 'AND',
            'or'  => 'OR'
        );
        $this->joins        = array();
        $this->tables_list  = array(array($table_name, $table_shortcut));
        $this->wheres       = array();
        $this->is_dirty     = true;
        $this->insert_type  = null;
        $this->limit_count  = null;
        $this->limit_start  = null;
        $this->wheres_count = 0;
    }

    /**
     * Monty_MySQL_Easy::__call()
     *
     * @param string $method_name The magic method called
     * @param array  $params      All sent parameters
     *
     * @return string $mixReturn
     */
    public function __call($method_name, $params)
    {
        if (substr($method_name, 0, 1) === '_') {
            trigger_error("$method_name is not a public method.", E_USER_ERROR);

            return '';
        }
        if (in_array($method_name, array_keys(self::$comparisons))) {
            $value_is_field
                = isset($params[2])
                ? $params[2]
                : false;

            return $this->where(
                $params[0],
                self::$comparisons[$method_name],
                $params[1],
                $value_is_field
            );
        }
        if (in_array($method_name, array_keys(self::$operators))) {
            if (count($params) == 1 && is_array($params[0])) {
                $params = $params[0];
            }

            return $this->mergeWheres(
                self::$operators[$method_name],
                $params
            );
        }
        trigger_error("$method_name is not a method.", E_USER_ERROR);

        return false;
    }

    /**
     * Monty_MySQL_Easy::add()
     *
     * @param string      $table_name     The name of the additional table
     * @param string|null $table_shortcut Optional shortcut letter for this table
     *
     * @return void
     */
    public function add($table_name, $table_shortcut = null)
    {
        $this->is_dirty      = true;
        $this->tables_list[] = array($table_name, $table_shortcut);
    }

    /**
     * Monty_MySQL_Easy::all()
     *
     * @param int $type The return type
     *
     * @return array $rows_array
     */
    public function all($type = null)
    {
        $this->buildQuery();

        return parent::all($type);
    }

    /**
     * Monty_MySQL_Easy::contains()
     *
     * @param string $field_name The column name to look into
     * @param string $value      The value to check for
     *
     * @return string
     */
    public function contains($field_name, $value)
    {
        return $this->where($field_name, 'LIKE', '%' . $value . '%');
    }

    /**
     * Monty_MySQL_Easy::delete()
     *
     * @return bool $boolHasSucceeded
     */
    public function delete()
    {
        $this->is_dirty = true;

        return $this->buildQuery(MONTY_QUERY_DELETE);
    }

    /**
     * Monty_MySQL_Easy::fields()
     *
     * @param array $fields_list The fields to return from this query
     *
     * @return void
     */
    public function fields($fields_list = array())
    {
        if (is_array($fields_list)) {
            $this->fields_list = $fields_list;
        } else {
            $this->fields_list = func_get_args();
        }
    }

    /**
     * Monty_MySQL_Easy::insert()
     *
     * @param array $fields_list The array or fields to insert the row with
     * @param int   $type        Whether to insert, insert ignore or replace into
     *
     * @return bool $boolHasSucceeded
     */
    public function insert($fields_list, $type = MONTY_INSERT_NORMAL)
    {
        $this->fields_list = $fields_list;
        $this->is_dirty    = true;
        $this->insert_type = $type;

        return $this->buildQuery(MONTY_QUERY_INSERT);
    }

    /**
     * Monty_MySQL_Easy::join()
     *
     * @param string      $table_name     The table to join with
     * @param string|null $table_shortcut Optional table shortcut
     * @param int         $intJoinType    Normal join, left or right join
     * @param string      $on_field_left  Field of the first linked table
     * @param string      $on_field_right Corresponding field of the second table
     *
     * @return void
     */
    public function join(
        $table_name,
        $table_shortcut,
        $intJoinType,
        $on_field_left,
        $on_field_right
    ) {
        $this->is_dirty = true;
        $this->joins[]  = array(
            $table_name,
            $table_shortcut,
            $intJoinType,
            $on_field_left,
            $on_field_right
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
        $this->is_dirty = true;
        if (func_num_args() == 1) {
            $this->limit_count = func_get_arg(0);
            $this->limit_start = 0;
        } elseif (func_num_args() == 2) {
            $this->limit_count = func_get_arg(1);
            $this->limit_start = func_get_arg(0);
        }
    }

    /**
     * Monty_MySQL_Easy::next()
     *
     * @param int $type The return type
     *
     * @return mixed $mixRow
     */
    public function next($type = null)
    {
        $this->buildQuery();

        return parent::next($type);
    }

    /**
     * Monty_MySQL_Easy::nextfield()
     *
     * @param mixed $field_data Optional field index
     *
     * @return mixed $field_data
     */
    public function nextfield($field_data = 0)
    {
        $this->buildQuery();

        return parent::nextfield($field_data);
    }

    /**
     * Monty_MySQL_Easy::query()
     *
     * @param string $query_string The SQL query to execute
     *
     * @return bool $boolHasSucceeded
     */
    public function query($query_string)
    {
        if (!parent::query($query_string)) {
            trigger_error($this->error(), E_USER_ERROR);

            return false;
        }

        return true;
    }

    /**
     * Monty_MySQL_Easy::queryall()
     *
     * @param string $query_string The SQL query to execute
     * @param int    $type         The return type
     *
     * @return array
     */
    public function queryall($query_string, $type = null)
    {
        $this->query($query_string);
        $this->is_dirty = false;

        return $this->all($type);
    }

    /**
     * Monty_MySQL_Easy::rand()
     *
     * @return void
     */
    public function rand()
    {
        $this->is_dirty = true;
        $this->sorts    = array(array(null, 1));
    }

    /**
     * Monty_MySQL_Easy::replace()
     *
     * @param array $fields_list The array of fields to replace the rows with
     *
     * @return bool $boolHasSucceeded
     */
    public function replace($fields_list)
    {
        return $this->insert($fields_list, MONTY_INSERT_REPLACE);
    }

    /**
     * Monty_MySQL_Easy::rows()
     *
     * @return int $number_rows
     */
    public function rows()
    {
        $this->buildQuery();

        return parent::rows();
    }

    /**
     * Monty_MySQL_Easy::seek()
     *
     * @param int $row_number The row number to set the pointer to
     *
     * @return bool $boolHasSucceeded
     */
    public function seek($row_number)
    {
        $this->buildQuery();

        return parent::seek($row_number);
    }

    /**
     * Monty_MySQL_Easy::sort()
     *
     * @param string $by     Column to sort the result by
     * @param int    $is_asc Whether to sort ascending or descending
     *
     * @return void
     */
    public function sort($by, $is_asc = 1)
    {
        $this->is_dirty = true;
        $this->sorts[]  = array($by, $is_asc);
    }

    /**
     * Monty_MySQL_Easy::sql()
     *
     * @param int $type The query type to generate
     *
     * @return string $query_string
     */
    public function sql($type = MONTY_QUERY_SELECT)
    {
        $this->buildQuery($type);

        return $this->query_string;
    }

    /**
     * Monty_MySQL_Easy::starts()
     *
     * @param string $field_name The field to compare with
     * @param string $value      The value to check for
     *
     * @return string
     */
    public function starts($field_name, $value)
    {
        return $this->where($field_name, 'LIKE', $value . '%');
    }

    /**
     * Monty_MySQL_Easy::truncate()
     *
     * @return bool $boolHasSucceeded
     */
    public function truncate()
    {
        $this->is_dirty = true;

        return $this->buildQuery(MONTY_QUERY_TRUNCATE);
    }

    /**
     * Monty_MySQL_Easy::update()
     *
     * @param array|string $fields_list The field(s) to update
     * @param string       $value       The data to insert into
     *
     * @return bool $boolHasSucceeded
     */
    public function update($fields_list, $value = null)
    {
        if ($value !== null) {
            $fields_list = array($fields_list => $value);
        }
        $this->fields_list = $fields_list;
        $this->is_dirty    = true;

        return $this->buildQuery(MONTY_QUERY_UPDATE);
    }

    /**
     * Monty_MySQL_Easy::where()
     *
     * @param string $field_name     Field to compare with
     * @param string $comparison     Comparison operator
     * @param mixed  $value          Data or field name to check for
     * @param bool   $value_is_field Whether $value is a field name
     *
     * @return string $hash
     */
    public function where(
        $field_name,
        $comparison,
        $value,
        $value_is_field = false
    ) {
        $this->is_dirty = true;
        $where_clause   = '';
        if (stristr($field_name, '.')) {
            $field_array = explode('.', $field_name, 2);
            $field_name  = $field_array[0] . '.`' . $field_array[1] . '`';
        } else {
            $field_name = '`' . $field_name . '`';
        }
        $where_clause .= ' ' . $field_name;
        if (is_null($value)) {
            if ($comparison == '=') {
                $where_clause .= ' IS';
            } else {
                $where_clause .= ' IS NOT';
            }
            $where_clause .= ' NULL';
        } elseif ($value_is_field) {
            $field_array = explode('.', $value, 2);
            $where_clause
                .= ' ' . $comparison
                . ' ' . $field_array[0]
                . '.`' . $field_array[1] . '`';
        } else {
            $where_clause
                .= ' ' . $comparison
                . ' "' . mysql_real_escape_string($value) . '"';
        }

        $hash                = $this->wheres_count++;
        $this->wheres[$hash] = $where_clause;

        return $hash;
    }

    /**
     * Monty_MySQL_Easy::buildQuery()
     *
     * @param int $type The query type to generate
     *
     * @return bool $boolHasSucceeded
     * @access protected
     */
    protected function buildQuery($type = MONTY_QUERY_SELECT)
    {
        if (!$this->is_dirty) {
            return false;
        }
        $query_string = '';
        switch ($type) {
        case MONTY_QUERY_SELECT:
            $query_string = 'SELECT ';
            if (count($this->fields_list)) {
                $query_string .= $this->buildQueryFields($type);
            } else {
                $query_string .= '*';
            }
            $query_string .= ' FROM';
            $i = 0;
            foreach ($this->tables_list as $table_array) {
                $query_string .= ' `' . $table_array[0] . '` ' . $table_array[1];
                if ($i + 1 < count($this->tables_list)) {
                    $query_string .= ',';
                }
                $i++;
            }
            $query_string .= $this->buildQueryJoins();
            $query_string .= $this->buildQueryWheres();
            $query_string .= $this->buildQuerySorts();
            $query_string .= $this->buildQueryLimit($type);
            break;

        case MONTY_QUERY_INSERT:
            switch ($this->insert_type) {
            case MONTY_INSERT_NORMAL:
                $query_string = 'INSERT INTO';
                break;
            case MONTY_INSERT_IGNORE:
                $query_string = 'INSERT IGNORE INTO';
                break;
            case MONTY_INSERT_REPLACE:
                $query_string = 'REPLACE INTO';
                break;
            }
            $query_string .= ' `' . $this->tables_list[0][0] . '`';
            $query_string .= $this->buildQueryFields($type);
            break;

        case MONTY_QUERY_UPDATE:
            $query_string = 'UPDATE';
            $query_string .= ' `' . $this->tables_list[0][0] . '`';
            $query_string .= $this->buildQueryFields($type);
            $query_string .= $this->buildQueryWheres();
            $query_string .= $this->buildQuerySorts();
            $query_string .= $this->buildQueryLimit($type);
            break;

        case MONTY_QUERY_DELETE:
            $query_string = 'DELETE FROM';
            $query_string .= ' `' . $this->tables_list[0][0] . '`';
            $query_string .= $this->buildQueryWheres();
            $query_string .= $this->buildQuerySorts();
            $query_string .= $this->buildQueryLimit($type);
            break;

        case MONTY_QUERY_TRUNCATE:
            $query_string = 'TRUNCATE';
            $query_string .= ' `' . $this->tables_list[0][0] . '`';
            break;
        }
        $this->is_dirty = false;

        return $this->query($query_string);
    }

    /**
     * Monty_MySQL_Easy::buildQueryFields()
     *
     * @param int $type The query type to generate
     *
     * @return string $field_names
     */
    protected function buildQueryFields($type)
    {
        $field_names = '';
        switch ($type) {
        case MONTY_QUERY_SELECT:
            $i = 0;
            foreach ($this->fields_list as $field_name) {
                if (stristr($field_name, '.')) {
                    $field_name_array = explode('.', $field_name, 2);
                    $field_name
                                      = $field_name_array[0] . '.`'
                        . $field_name_array[1] . '`';
                } else {
                    $field_name = '`' . $field_name . '`';
                }
                $field_names .= ' ' . $field_name;
                if ($i + 1 < count($this->fields_list)) {
                    $field_names .= ',';
                }
                $i++;
            }
            break;

        case MONTY_QUERY_INSERT:
        case MONTY_QUERY_UPDATE:
        case MONTY_QUERY_DELETE:
            $field_names = ' SET';
            $i           = 0;
            foreach ($this->fields_list as $field_name => $value) {
                $field_array = array($field_name, $value);
                if (stristr($field_array[0], '.')) {
                    $field_name_array = explode('.', $field_array[0], 2);
                    $field_name       = '`' . $field_name_array[1] . '`';
                } else {
                    $field_name = '`' . $field_array[0] . '`';
                }
                $field_names .= ' ' . $field_name . ' =';
                if (is_null($field_array[1])) {
                    $field_names .= ' NULL';
                } else {
                    $field_names
                        .= ' "'
                        . mysql_real_escape_string($field_array[1])
                        . '"';
                }
                if ($i + 1 < count($this->fields_list)) {
                    $field_names .= ',';
                }
                $i++;
            }
            break;
        }

        return $field_names;
    }

    /**
     * Monty_MySQL_Easy::buildQueryLimit()
     *
     * @param int $type Type of the query to generate
     *
     * @return string $strLimit
     */
    protected function buildQueryLimit($type)
    {
        $strLimit = '';
        if ($this->limit_start !== null) {
            $strLimit
                = ' LIMIT ' . $this->limit_start . ', '
                . $this->limit_count;
        }
        if ($type != MONTY_QUERY_SELECT && $this->limit_count !== null) {
            $strLimit = ' LIMIT ' . $this->limit_count;
        }

        return $strLimit;
    }

    /**
     * Monty_MySQL_Easy::buildQuerySorts()
     *
     * @return string $sorts
     */
    protected function buildQuerySorts()
    {
        $sorts = '';
        if (count($this->sorts)) {
            $sorts .= ' ORDER BY';
            for ($i = 0; $i < count($this->sorts); $i++) {
                $sort = $this->sorts[$i];
                if ($sort[0] !== null) {
                    if (stristr($sort[0], '.')) {
                        $field_array = explode('.', $sort[0], 2);
                        $field_name  = $field_array[0] . '.`' . $field_array[1] . '`';
                    } else {
                        $field_name = '`' . $sort[0] . '`';
                    }
                    $sorts .= ' ' . $field_name;
                    if ($sort[1] < 0) {
                        $sorts .= ' DESC';
                    } else {
                        $sorts .= ' ASC';
                    }
                } else {
                    $sorts .= ' RAND()';
                }
                if ($i + 1 < count($this->sorts)) {
                    $sorts .= ',';
                }
            }
        }

        return $sorts;
    }

    /**
     * Monty_MySQL_Easy::buildQueryJoins()
     *
     * @return string $joins
     */
    protected function buildQueryJoins()
    {
        $joins = '';
        if (count($this->joins)) {
            foreach ($this->joins as $join) {
                $intJoinType = $join[2];
                switch ($intJoinType) {
                case MONTY_JOIN_NORMAL:
                    $joins .= ' JOIN';
                    break;
                case MONTY_JOIN_LEFT:
                    $joins .= ' LEFT JOIN';
                    break;
                case MONTY_JOIN_RIGHT:
                    $joins .= ' RIGHT JOIN';
                    break;
                }
                $joins .= ' `' . $join[0] . '` ' . $join[1];
                $joins .= ' ON (';
                $on_left  = explode('.', $join[3]);
                $on_right = explode('.', $join[4]);
                $joins .= ' ' . $on_left[0] . '.`' . $on_left[1] . '`';
                $joins .= ' = ' . $on_right[0] . '.`' . $on_right[1] . '`)';
            }
        }

        return $joins;
    }

    /**
     * Monty_MySQL_Easy::buildQueryWheres()
     *
     * @return string $where_clauses
     */
    protected function buildQueryWheres()
    {
        $where_clauses = '';
        if (count($this->wheres)) {
            $hash = $this->mergeWheres('AND', array_keys($this->wheres));
            $where_clauses .= ' WHERE';
            $where_clauses .= $this->wheres[$hash];
        }

        return $where_clauses;
    }

    /**
     * Monty_MySQL_Easy::mergeWheres()
     *
     * @param string $operator Boolean operator to use for merging conditions
     * @param array  $wheres   Array of conditions
     *
     * @return string
     */
    protected function mergeWheres($operator, $wheres)
    {
        if (!count($wheres)) {
            return '';
        } elseif (count($wheres) == 1) {
            $wheres = array_values($wheres);

            return $wheres[0];
        }

        $where_clauses = ' (';
        $i             = 0;
        foreach ($wheres as $hash) {
            $where_clauses .= $this->wheres[$hash];
            unset($this->wheres[$hash]);
            if ($i + 1 < count($wheres)) {
                $where_clauses .= ' ' . $operator;
            }
            $i++;
        }

        $where_clauses .= ' )';

        $hash                = $this->wheres_count++;
        $this->wheres[$hash] = $where_clauses;

        return $hash;
    }
}
