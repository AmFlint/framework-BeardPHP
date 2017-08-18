<?php

namespace Helper;


use Exception;

class ModelHandler
{
    /**
     * Method used to generate a model entity from data and model name given
     * @param array $data
     * @param string $model
     * @return Model
     */
    public static function generateEntity($data, $model)
    {
        $className = 'Model\\' . $model;

        self::checkParameters($data, $className);

        return $model = new $className($data);
    }

    /**
     * Method used to generate multiple model entities from data and model name given
     * @param array $data
     * @param string $model
     * @throws Exception
     * @return Model[]
     */
    public static function generateEntities($data, $model)
    {
        $className = 'Model\\' . $model;

        self::checkParameters($data, $className);

        $entities = [];
        foreach ($data as $item)
        {
            if (!is_array($item))
            {
                throw new Exception(
                    'Data passed to generate an entity must be associative array'
                );
            }
            $entities[] = new $className($item);
        }

        return $entities;
    }

    /**
     * Check if Parameters are correct internally to the class functionnalities
     * @param array $data
     * @param string $modelName
     * @throws Exception
     */
    protected static function checkParameters($data, $modelName)
    {
        if (!is_array($data))
        {
            throw new Exception(
                'Data passed to generate an entity must be associative array'
            );
        }

        if (!class_exists($modelName))
        {
            throw new Exception('Entity to generate: Model class does not exist.');
        }
    }
}
