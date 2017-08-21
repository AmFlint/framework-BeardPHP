<?php

namespace Controller;

use Helper\Controller;
use Helper\Form;
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
//        $cluster = Cluster::create(['name' => 'masssssss', 'birthday' => '2017-02-19']);
//        var_dump($user->cluster);
        $form = new Form();
        $form->validate();
    }

    public function testAction($id, $test)
    {

    }
}