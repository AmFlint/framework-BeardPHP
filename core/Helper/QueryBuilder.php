<?php

namespace Helper;

use helpers\Random;
use PDO;

class QueryBuilder
{
    /**
     * @var
     */
    public $parameters;

    public $order;
    /**
     * @var
     */
    public $condition;
    /**
     * @var
     */
    public $query;
    /**
     * @var
     */
    public $table;
    /**
     * @var
     */
    public $db;
    /**
     * @var
     */
    public $values;
    /**
     * @var
     */
    public $array_parameters;
    /**
     * @var
     */
    protected $stmt;
    /**
     * @var
     */
    public $limit;
    /**
     * @var
     */
    public $offset;

    public $columns;

    public $joint;

    public $joint_parameters;

    /**
     * QueryBuilder constructor.
     * @param string $tableName - table name to bind to QB instance.
     * Assigns DB table to the builder object
     */
    public function __construct($tableName)
    {
        $this->db = DB::get();
        $this->resetQuery();
        $this->table($tableName);
    }

    /**
     * @param string $table
     * Used to set the table we are going to work on
     */
    public function table(string $table)
    {
        $this->table = $table;
    }

    /**
     * @param array $param
     * @param array $alias
     * @return $this
     * First array of parameters contains the columns affected and $alias the alias associated to each parameter
     */
    public function select(array $param, array $alias = array())
    {
        $this->parameters = $param[0];
        if (isset($alias[0]) && trim($alias[0]) != '') {
            $this->parameters .= ' AS ' . $alias[0];
        }

        $count = count($param);
        if ($count < 2) {
            return $this;
        }
        for ($i = 1; $i < $count; $i++) {
            $this->parameters .= ", " . $param[$i];
            if (isset($alias[$i]) && trim($alias[$i]) != '') {
                $this->parameters .= ' AS ' . $alias[$i];
            }
        }

        return $this;
    }

    public function where($condition, $operator = '=')
    {
        $this->condition = ' WHERE ';
        return $this->manageCondition($condition, $operator);
    }

    public function andWhere($condition, $operator = '=')
    {
        $this->condition .= ' AND ';
        return $this->manageCondition($condition, $operator);
    }

    public function orWhere($condition, $operator = '=')
    {
        $this->condition .= ' OR ';
        return $this->manageCondition($condition, $operator);
    }

    public function resetCondition()
    {
        $this->condition = '';
        $this->values = $this->array_parameters = [];
        return $this;
    }

    protected function manageCondition($condition, $operator)
    {
        $queryParam = key($condition);
        if (is_array(current($condition)))
        {
            $values = current($condition);
            $operator = $operator == '=' | 'IN' ? 'IN' : 'NOT IN';
            $to_bind = " ('{$values[0]}'";
            for ($i = 1; $i < count($values); $i++)
            {
                $to_bind .= ", '{$values[$i]}'";
            }
            $to_bind .= ')';
        }
        else
        {
            $queryValue = current($condition);
            $to_bind = implode('', explode('.', $queryParam));
            if (in_array($to_bind, $this->array_parameters))
            {
                $to_bind .= Random::generateRandomString(5);
            }
            array_push($this->values, $queryValue);
            array_push($this->array_parameters, $to_bind);
            $operator .= ' :';
        }
        $this->condition .= "{$queryParam} {$operator}{$to_bind}";
        return $this;
    }

    public function join($table, $type)
    {
        if (trim($this->joint) != '') { // if function "on()" called before join
            $this->joint .= ' ';
        }
        $this->joint .= ' ' . strtoupper($type) . ' JOIN ' . $table . ' ';
        return $this;
    }

    /**
     * Joining two tables with Left Join
     * @param string $table - name of the table to join
     * @param String[] $on - associative array for table joint
     * of form ["table1.parameter" => "table2.parameter"]
     * @return QueryBuilder $this - instance of QueryBuilder
     */
    public function leftJoin($table, $on)
    {
        return $this->join($table, 'LEFT')
        ->on(key($on), current($on));
    }

    /**
     * Joining with "Right join" between two tables
     * @param string $table - name of the table to join
     * @param String[] $on - associative array for table joint
     * of form ["table1.parameter" => "table2.parameter"]
     * @return QueryBuilder $this - instance of QueryBuilder
     */
    public function rightJoin($table, $on)
    {
        return $this->join($table, 'RIGHT')
            ->on(key($on), current($on));
    }

    /**
     * Joining with "Right join" between two tables
     * @param string $table - name of the table to join
     * @param String[] $on - associative array for table joint
     * of form ["table1.parameter" => "table2.parameter"]
     * @return QueryBuilder $this - instance of QueryBuilder
     */
    public function innerJoin($table, $on)
    {
        return $this->join($table, 'INNER')
            ->on(key($on), current($on));
    }

    /**
     * @param string $parameter1
     * @param string $parameter2
     * @return $this
     * Both parameters are the columns from the table you want to join (strings), concats a string to the variable containing the whole joint
     * Example : table1.id, table2.linked_id -> output : ' ON table1.id = table2.linked_id'
     */
    public function on(string $parameter1, string $parameter2)
    {
        $this->joint .= 'ON ' . $parameter1 . ' = ' . $parameter2;
        $this->setJointParameterValue($parameter2);
        return $this;
    }

    protected function setJointParameterValue($value)
    {
        $table = $this->getTableNameFromValue($value);
        $this->joint_parameters[$table] = $value;
    }

    protected function getTableNameFromValue($value)
    {
        $parameter = explode('.', $value);
        return $parameter[0];
    }

    /**
     * @param $parameter1
     * @param $parameter2
     * @param $condition
     * @return $this
     *
     */
    public function andOr($parameter1, $parameter2, $condition)
    {
        $this->joint .= ' ' . $condition . ' ' . $parameter1 . ' = ' . $parameter2;
        return $this;
    }

    /**
     * @param $num_start
     * @param bool $num_end
     * @return $this
     */
    public function limit($num_start, $num_end = false)
    {
        $this->limit = ' LIMIT ' .$num_start;
        if ($num_end) {
            $this->limit .= ', ' . $num_end;
        }
        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = " OFFSET " . $offset;
        return $this;
    }

    /**
     * @param array $array_param
     * @return $this
     */
    public function addColumns(array $array_param)
    {
        $this->columns = ' ( ' . $array_param[0];
        array_push($this->array_parameters, $array_param[0]);
        $count = count($array_param);
        for ($i = 1; $i < $count; $i++) {
            $this->columns .= ", " . $array_param[$i];
            array_push($this->array_parameters, $array_param[$i]);
        }
        $this->columns .= ') VALUES( :' . $array_param[0];
        for ($i = 1; $i < $count; $i++) {
            $this->columns .= ", :" . $array_param[$i];
        }
        $this->columns .= ') ';
        return $this;
    }

    /**
     * @param array $array_param
     * @return QueryBuilder
     */
    public function updateColumns(array $array_param)
    {
        $this->columns = $array_param[0] . ' = :' . $array_param[0];
        array_push($this->array_parameters, $array_param[0]);
        $count = count($array_param);
        if ($count < 2) {
            return $this;
        }
        for ($i = 1; $i < $count; $i++) {
            $this->columns .= ', ' . $array_param[$i] . ' = :' . $array_param[$i];
            array_push($this->array_parameters, $array_param[$i]);
        }
        return $this;
    }

    /**
     * @param array $arrayParams
     * @return $this
     */
    public function values(array $arrayParams)
    {
        foreach ($arrayParams as $value) {
            array_push($this->values, $value);
        }
        return $this;
    }

    /**
     * @param string $crud
     */
    protected function setQuery($crud = "select")
    {
        if ($crud == "select") {
            $this->query = 'SELECT ' . $this->parameters .' FROM ' . $this->table . $this->joint . $this->condition .  $this->order . $this->limit . $this->offset;
        } else if ($crud == "add") {
            $this->query =  'INSERT INTO ' . $this->table . $this->columns;
        } else if ($crud == 'update') {
            $this->query = 'UPDATE ' . $this->table . ' SET ' . $this->columns . $this->condition;
        } else if ($crud == 'delete') {
            $this->query = 'DELETE FROM ' . $this->table . $this->condition . $this->limit;
        }
    }

    /**
     *
     */
    protected function resetQuery()
    {
        $this->parameters = $this->columns = $this->query = $this->condition = $this->joint = $this->stmt = $this->limit = $this->offset = '';
        $this->values = $this->array_parameters = $this->joint_parameters = array();
    }

    /**
     *
     */
    protected function bind()
    {
        $count = count($this->values);
        for ($i = 0; $i < $count; $i++) {
            $this->stmt->bindValue(':'.$this->array_parameters[$i], htmlspecialchars($this->values[$i]));
        }
    }

    public function orderBy($attribut, $sens)
    {
        $this->order = ' ORDER BY ' . $attribut . ' ' . $sens;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function resultSet()
    {
        $this->stmt->execute();
        $row = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->resetQuery();
        return $row;
    }

    /**
     * @return string
     */
    public function count()
    {
        $this->parameters = "COUNT(*)";
        $this->limit = $this->offset = '';
        $this->setQuery();
        $this->stmt = $this->db->prepare($this->query);
        $this->bind();
        $this->stmt->execute();
        $row = $this->stmt->fetchColumn();
        $this->resetQuery();
        return $row;
    }

    /**
     * @return array - data retrieved from DB or empty array.
     */
    public function getAll()
    {
        $this->condition = ' WHERE 1';
        $this->setQuery();
        $this->stmt = $this->db->prepare($this->query);
        $row = $this->resultSet();
        return $row;
    }

    /**
     * @return array - data retrieved from DB or empty array.
     */
    public function getFirst()
    {
        $this->condition = "";
        return $this->getOne();
    }

    public function getOne()
    {
        $this->limit = ' LIMIT 1';
        $this->setQuery();
        $this->stmt = $this->db->prepare($this->query);
        $this->bind();
        $row = $this->resultSet();
        return $row;
    }

    /**
     * @return array - data retrieved from database, or empty array.
     */
    public function get()
    {
        $this->setQuery();
        $this->stmt = $this->db->prepare($this->query);
        $this->bind();
        $row = $this->resultSet();
        return $row;
    }

    /**
     *
     */
    public function add()
    {
        $this->setQuery('add');
        $this->stmt = $this->db->prepare($this->query);
        $this->bind();
        $this->stmt->execute();
        $this->resetQuery();
    }

    /**
     *
     */
    public function update()
    {
        $this->setQuery('update');
        $this->stmt = $this->db->prepare($this->query);
        $this->bind();
        $this->stmt->execute();
        $this->resetQuery();
    }

    /**
     *
     */
    public function delete()
    {
        $this->limit = " LIMIT 1";
        $this->setQuery('delete');
        $this->stmt = $this->db->prepare($this->query);
        $this->bind();
        $this->stmt->execute();
        $this->resetQuery();
    }

    /**
     * @param string $crud
     * Function used for SQL Query Debugging, pass action as a parameter to set the right query
     */
    public function debugQuery(string $crud = 'select')
    {
        if ($crud == 'delete') {
            $this->limit = " LIMIT 1";
        }
        $this->setQuery($crud);
        $export['query'] = $this->query;
        $export['values'] = $this->values;
        $export['parameters'] = $this->array_parameters;
        $export['joint'] = $this->joint_parameters;
        dd($export);
    }

    /**
     * Set a custom hand-written SQL query.
     * @param string $query - Containing SQL Query
     */
    public function sqlCommand($query)
    {
        $this->query = $query;
    }
}
