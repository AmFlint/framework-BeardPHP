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
        $user = Cluster::findOne(1);
        $user->delete();
//        var_dump($user->cluster);

    }

    public function testAction($id, $test)
    {

    }
}