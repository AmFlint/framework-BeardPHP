<?php

namespace Controller;

use Helper\Controller;
use Model\Cluster;
use Model\User;

class DefaultController extends Controller
{

    public function fooAction()
    {
        echo "salut";
    }

    public function chickAction()
    {
        $cluster = Cluster::withUsers()->find()->getArray();
        var_dump($cluster);
//        var_dump($cluster->users);
//        $user = User::findOne(1);
//        var_dump($user->cluster);

    }

    public function testAction($id, $test)
    {

    }
}