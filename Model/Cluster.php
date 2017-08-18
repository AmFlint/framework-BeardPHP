<?php

namespace Model;

use Helper\Model;

class Cluster extends Model
{

    public $id;
    public $name;
    public $birthday;

    public function getUsers()
    {
        return $this->hasMany('User', ['id' => 'cluster_id']);
    }

}