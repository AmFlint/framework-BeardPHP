<?php

namespace Controller;

use Helper\Controller;
use Model\Cluster;

class DefaultController extends Controller
{

    public function fooAction()
    {
        echo "salut";
    }

    public function chickAction()
    {
        $cluster = Cluster::findOne(1);
        $cluster->user;
    }

    public function testAction($id, $test)
    {

    }
}