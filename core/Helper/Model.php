<?php

namespace Helper;

abstract class Model
{
    /**
     * @var string - The entity's DB table name
     */
    public static $tableName = null;

    /**
     * @var QueryBuilder|BeardQuery - The entity's QueryBuilder
     */
    protected static $qb = null;

    /**
     * @var string - represents the DB table primary key linked to the Entity
     */
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
     * @param array $attributes - key => value to assign to current Entity
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
     * @return array - Current Entity's attributes
     */
    public function getAttributes()
    {
        return get_object_vars($this);
    }

    /**
     * Get current Model's properties name
     * @return array - properties name of current Model
     */
    public static function getProperties()
    {
        $class = ModelHandler::className(self::className());
        /* @var Model $model */
        $model = new $class();
        return array_keys($model->getAttributes());
    }

    /**
     * Get an instance of BeardQueryBuilder associated with Model's table name
     * @return BeardQuery - Instance of Entity's specific QueryBuilder
     */
    public static function getQueryBuilder()
    {
        if (!is_null(self::$qb)) {
            return self::$qb;
        }
        $table = self::getTableName();
        return self::$qb = new BeardQuery($table);
    }

    /**
     * Method to get an instance of Framework's Native Query builder (different methods)
     * @return QueryBuilder - Native Query Builder Object
     */
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
     * @param string $table - Table name to convert
     * @return string - Entity name without namespaces
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
     * @return string - current model's class name once namespaces are cleared
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
     * Relationship creation method for a x to 1 relationship (x might be 1 or many)
     * @param string $model - The model name to link via the relationship
     * @param array $joint - key => value representing link between both DB tables
     * @return Model - The Entity fetched via the relationship
     * @throws \Exception
     */
    public function hasOne($model, $joint)
    {
        $data = $this->setRelationship($model, $joint)->getOne();

        $model = ModelHandler::generateEntity(current($data), $model);
        return $model;
    }

    /**
     * Relationship creation method for a x to n relationship (x might be 1 or many)
     * @param string $model - The model name to link via the relationship
     * @param array $joint - key => value representing link between both DB tables
     * @return Collection - collection of entities fetched.
     */
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
        $conditionAttr = $tableLeftAttribute;
        if (!isset($this->{$tableLeftAttribute}))
        {
            $conditionAttr = self::$primaryKey;
        }

        $condition = [
            "{$tableLeft}.{$conditionAttr}" => $this->{$conditionAttr} ?? 0
        ];
        $modelInstance = new $modelClass();
        $this->setSelectClause($tableRight, $modelInstance->getProperties());

        return $this->getQueryBuilder()
            ->join($tableRight, 'inner')
            ->setRelationshipJoin("{$tableLeft}.{$tableLeftAttribute}", "{$tableRight}.{$tableRightAttribute}")
            ->where($condition);
    }

    protected function setSelectClause($table, $properties)
    {
        $select = [];
        foreach ($properties as $property)
        {
            $select[] = "{$table}.{$property}";
        }
        $this->getQueryBuilder()
        ->select($select);
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

    public static function findAll($parameters)
    {
        
    }

    public static function exists($parameter)
    {
//        return self::findOne($parameter)
    }

    /**
     * Init a 'Select' Query, if associative array is passed, 'where' clause will be
     * initialized with current key/value, else if a single value is passed, 'where'
     * clause will target current Model's mainKey property with value passed.
     * @param bool|array|integer|string $parameter
     * @return QueryBuilder - instance of QB
     */
    public static function find($parameter = false)
    {
        $qb = self::getQueryBuilder()->select(['*']);

        if (is_array($parameter))
        {
            $qb->where($parameter);
        }
        else if ($parameter)
        {
            $qb->where([self::$primaryKey => $parameter]);
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

    public function viaTable($table, $joint)
    {
        /** @var Model $modelClass*/
//        $this->checkRelationshipParams($modelClass = $table, $joint);
        $tableLeft = self::getTableName();
        $tableRight = $table;
        $tableLeftAttribute = key($joint);
        $tableRightAttribute = current($joint);
        self::getQueryBuilder()
            ->select(['*'])
            ->join($tableRight, 'inner')
            ->on("{$tableLeft}.{$tableLeftAttribute}", "{$tableRight}.{$tableRightAttribute}");

        return $this;
    }
}
