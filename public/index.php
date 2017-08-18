<?php

//var_dump($_SERVER['REQUEST_URI']);

require '../vendor/autoload.php';

$route = new \Helper\Router($_SERVER['REQUEST_URI']);
$route::$url = trim($route::$url, '/');

$route->init();

$currentRoute = $route->getRoute();
$route::generateController($currentRoute);