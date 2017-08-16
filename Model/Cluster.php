<?php

namespace Model;

use Helper\Model;

class Cluster extends Model
{

    public $id;
    public $name;
    public $birthday;

    public function getUser()
    {
        return $this->hasOne('User', ['id', 'cluster_id']);
    }

}