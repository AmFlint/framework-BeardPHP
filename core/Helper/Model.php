<?php

namespace Helper;

abstract class Model
{
    public static $tableName = null;
    protected static $qb = null;

    public function __construct($attributes)
    {
        $this->getQueryBuilder();
        $this->hydrate($attributes);
    }

    /**
     * Hydrating Model's properties from given datas if property exists
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

    public function getAttributes()
    {
        return get_object_vars($this);
    }

    protected static function getQueryBuilder()
    {
        if (!is_null(self::$qb)) {
            return self::$qb;
        }
        $table = self::getTableName();
        return self::$qb = new QueryBuilder($table);
    }

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
     * Return the name Model class without Namespace
     * @return string
     */
    public static function className()
    {
        $className = get_called_class();
        return trim(substr($className, strpos($className, '\\') + 1));
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    public function hasOne($model, $joint)
    {
        if (!class_exists($modelClass = 'Model\\' . $model)) {
            //TODO manage errors
            throw new \Exception('Classe à joindre inexistante');
        }

        if (!is_array($joint) && count($joint) < 2) {
            throw new \Exception('Le joint entre les tables doit être un tableau');
        }

        $tableLeft = self::getTableName();
        $tableRight = $modelClass::getTableName();
        $data = $this->getQueryBuilder()
            ->select(['*'])
            ->join($tableRight, 'right')
            ->on("{$tableLeft}.{$joint[0]}", "{$tableRight}.{$joint[1]}")
            ->where("{$tableLeft}.{$joint[0]}", $this->{$joint[0]})
            ->getFirst();

        $model = new $modelClass($data);
        var_dump($model->getAttributes()); die();
    }

    public static function findOne($id)
    {
        $data = self::getQueryBuilder()
            ->select(['*'])
            ->where('id', $id)
            ->get();

        $className = 'Model\\' . self::className();

        return $model = new $className(current($data));
    }
}
