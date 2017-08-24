<?php

namespace Helper;

abstract class Model
{
    public static $tableName = null;
    protected static $qb = null;
    protected static $primaryKey = 'id';

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
        // Remove all namespaces in class name
        while (strpos($className, '\\'))
        {
            $className = trim(substr($className, strpos($className, '\\') + 1));
        }
        return $className;
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
            return $this->$name = $this->$method();
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
        /** @var Model $modelClass*/
        $this->checkRelationshipParams($modelClass = ModelHandler::className($model), $joint);
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
        $key = self::$primaryKey;
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
            $qb->where(self::$primaryKey, $parameter);
        }

        return $qb;
    }

    /**
     * Method to create an instance of the model class called an persist it
     * in the DataBase
     * @param array $attributes - Attributes name => values to assign to the model to create
     * @return bool|Model - returns an instance of the Model class called if persisted, else false
     */
    public static function create($attributes)
    {
        $model = self::class;
        /** @var Model $entity */
        $entity = new $model($attributes);
        if ($entity->save())
        {
            return $entity;
        }
        return false;
    }

    /**
     * Update current Model instance according to given properties/values.
     * @param array $attributes - Attributes name => values for properties to update
     * @return boolean - true if entity is successfully persisted else false
     */
    public function update($attributes)
    {
        $this->hydrate($attributes);
        if ($this->save())
        {
            return true;
        }
        return false;
    }

    /**
     * Method to persist current instance to database
     * Create a new row if new object, else update single row
     * @return boolean - true if entity is successfully persisted, else false
     */
    public function save()
    {
        $this->users;
        $attributes = $this->getAttributes();
//        unset($attributes[self::$primaryKey]);
        $qb = $this->getQueryBuilder();
        $parameters = [];
        foreach ($attributes as $attribute => $value)
        {
            if (!is_object($value))
            {
                $parameters[$attribute] = $value;
            }
        }

        $qb->values(array_values($parameters));
        // If current model is not registered in database
        if (empty($parameters[self::$primaryKey]))
        {
            $qb->addColumns(array_keys($parameters))->add();
        }
        else
        {
            $qb
                ->updateColumns(array_keys($parameters))
                ->where([self::$primaryKey => $parameters[self::$primaryKey]])
                ->update();
        }

        return true;
    }

    /**
     * Method to delete current entity from database based on its primary key value
     * @return bool - true if entity has been successfully deleted from database, false if encountered a problem
     */
    public function delete()
    {
        $attributes = $this->getAttributes();
        // if current entity has not been persisted in the database
        if (empty($attributes[self::$primaryKey]))
        {
            return false;
        }
        $this->getQueryBuilder()
            ->where([
                self::$primaryKey => $attributes[self::$primaryKey]
            ])
            ->delete();

        return true;
    }
}
