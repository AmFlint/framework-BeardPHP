<?php

namespace Helper;

abstract class Model
{
    public static $tableName = null;
    protected static $qb = null;
    protected static $mainKey = 'id';

    /**
     * Model constructor.
     * @param $attributes
     */
    public function __construct($attributes = [])
    {
        $this->hydrate($attributes);
        return $this;
    }

    /**
     * Hydrating Model's properties from given data if property exists
     * @param array $attributes
     */
    protected function hydrate($attributes)
    {
        foreach ($attributes as $attribute => $value) {
            if (property_exists(get_called_class(), $attribute)) {
                $this->{$attribute} = $value;
            }
        }
    }

    /**
     * Get Entity's attributes as associative array.
     * @return array
     */
    public function getAttributes()
    {
        return get_object_vars($this);
    }

    /**
     * Get an instance of BeardQueryBuilder associated with Model's table name
     * @return QueryBuilder
     */
    public static function getQueryBuilder()
    {
        if (!is_null(self::$qb)) {
            return self::$qb;
        }
        $table = self::getTableName();
        return self::$qb = new BeardQuery($table);
    }

    public static function getNativeQueryBuilder()
    {
        $table = self::getTableName();
        return new QueryBuilder($table);
    }

    /**
     * Get table name associated with called Model Entity
     * @return string
     */
    public static function getTableName()
    {
        if (!is_null(static::$tableName)) {
            return static::$tableName;
        }

        $tableName = strtolower(self::className());
        $lastCharTable = substr($tableName, -1);

        $tableNameEnd = '';
        if (in_array($lastCharTable, ['y'])) {
            $tableNameEnd .= 'ie';
        } else {
            $tableNameEnd .= $lastCharTable;
        }
        $tableNameEnd .= 's';
        $table = substr($tableName, 0, -1). $tableNameEnd;

        return $table;
    }

    /**
     * Method to convert a table name to a Model name according to the framework's conventions.
     * @param string $table
     * @return string
     */
    public static function getEntityNameFromTable($table)
    {
        // ex: 'companies' table would give 'Company' Model
        if (substr($table,-3 ) === 'ies')
        {
            $table = substr($table, 0, -3) . 'y';
        } else {
            $table = substr($table, 0, -1);
        }

        return ucfirst($table);
    }

    /**
     * Return the name Model class without Namespace
     * @return string
     */
    public static function className()
    {
        $className = get_called_class();
        return trim(substr($className, strpos($className, '\\') + 1));
    }

    /**
     * Magic Method to manage call to relationships/special attributes treatment
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $relationship = $this->$name = $this->$method();
        }
    }

    /**
     * @param string $model
     * @param array $joint
     * @return Model
     * @throws \Exception
     */
    public function hasOne($model, $joint)
    {
        $data = $this->setRelationship($model, $joint)->getOne();

        $model = ModelHandler::generateEntity(current($data), $model);
        return $model;
    }

    public function hasMany($model, $joint)
    {
        return $this->setRelationship($model, $joint)->get($model);
    }

    protected function setRelationship($model, $joint)
    {
        $this->checkRelationshipParams($modelClass = 'Model\\' . $model, $joint);
        $tableLeft = self::getTableName();
        $tableRight = $modelClass::getTableName();
        $tableLeftAttribute = key($joint);
        $tableRightAttribute = current($joint);
        $condition = [
            "{$tableLeft}.{$tableLeftAttribute}" => $this->{$tableLeftAttribute} ?? 0
        ];
        return $this->getQueryBuilder()
            ->select(['*'])
            ->join($tableRight, 'inner')
            ->on("{$tableLeft}.{$tableLeftAttribute}", "{$tableRight}.{$tableRightAttribute}")
            ->where($condition);
    }

    protected function checkRelationshipParams($model, $joint)
    {
        if (!class_exists($model)) {
            throw new \Exception('Classe à joindre inexistante');
        }

        if (!is_array($joint)) {
            throw new \Exception('The joint between entities must be of type `array`.');
        }
    }

    /**
     * Get a single entity from database according to model's mainKey and given parameter
     * @param array|string|integer $parameter
     * @return Model
     */
    public static function findOne($parameter)
    {
        $key = self::$mainKey;
        $value = $parameter;

        if (is_array($parameter)) {
            $key = key($parameter);
            $value = current($parameter);
        }

        $data = self::getQueryBuilder()
            ->select(['*'])
            ->where([$key => $value])
            ->getOne();

        return ModelHandler::generateEntity(current($data), self::className());
    }

    /**
     * Init a 'Select' Query, if associative array is passed, 'where' clause will be
     * initialized with current key/value, else if a single value is passed, 'where'
     * clause will target current Model's mainKey property with value passed.
     * @param bool|array|integer|string $parameter
     * @return QueryBuilder
     */
    public static function find($parameter = false)
    {
        $qb = self::getQueryBuilder()->select(['*']);

        if (is_array($parameter))
        {
            $qb->where(key($parameter), current($parameter));
        }
        else if ($parameter)
        {
            $qb->where(self::$mainKey, $parameter);
        }

        return $qb;
    }
}
