<?php
namespace Model;

use Helper\Model;

class User extends Model
{

    public $id;
    public $name;
    public $email;
    public $lastname;
    public $cluster_id;
    public $birthday;

    public function getCluster()
    {
        return $this->hasOne('Cluster', ['cluster_id' =>'id']);
    }

}