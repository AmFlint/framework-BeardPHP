<?php

namespace Helper;

use helpers\Random;
use PDO;

/**
 * Class BeardQuery
 * Query Builder managing Collection Objects
 * @package Helper
 */
class BeardQuery extends QueryBuilder
{
    /**
     * @return mixed
     */
    public function get($model = false)
    {
        $this->setQuery();
        $this->stmt = $this->db->prepare($this->query);
        $this->bind();
        $row = $this->resultSet();
        $model = !$model ? Model::getEntityNameFromTable($this->table) : $model;
        return new Collection($row, $model);
    }

    public function getArray()
    {
        return parent::get();
    }

    public function setRelationshipJoin($leftAttribute, $rightAttribute)
    {
        $table = $this->getTableNameFromValue($leftAttribute);
        if (count($this->joint_parameters) > 0 && !isset($this->joint_parameters[$table]))
        {
            $leftAttr = explode('.', $leftAttribute);
            $leftAttribute = key($this->joint_parameters) . ".{$leftAttr[1]}";
        }

        return $this->on($leftAttribute, $rightAttribute);
    }
}
