<?php

//var_dump($_SERVER['REQUEST_URI']);

require '../vendor/autoload.php';
try {
    $route = new \Helper\Router($_SERVER['REQUEST_URI']);
    $route::$url = trim($route::$url, '/');
    if (strpos($route::$url, '?'))
    {
        $route::$url = substr($route::$url, 0, strpos($route::$url, '?'));
    }
    $route->init();

    $currentRoute = $route->getRoute();
    $route::generateController($currentRoute);
} catch (\Exception $e) {
    dd($e->getMessage());
}
