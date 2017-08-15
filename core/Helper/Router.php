<?php

namespace Helper;


class Router
{
    public static $url;
    public static $routeCollection;
    const ROUTE_FILE = APP_ROUTE_DIR . "/routing.json";
    const ROUTE_ALL_METHODS = 'ALL';

    public function __construct($url)
    {
        self::$url = $url;
    }

    public static function init()
    {
        $routes_list = json_decode(file_get_contents(self::ROUTE_FILE));

        // Load list of routes
        foreach ($routes_list as $routeIdentifier => $route) {
            if (isset($route->method)) {
                self::$routeCollection[$route->method][$routeIdentifier] = $route;
            } else {
                self::$routeCollection[self::ROUTE_ALL_METHODS][$routeIdentifier] = $route;
            }
        }
    }

    public static function getRoute()
    {
        foreach (self::$routeCollection[$_SERVER['REQUEST_METHOD']] as $route) {
            if (Route::match($route->path, self::$url)) {
                return $route;
            }
        }
        foreach (self::$routeCollection[self::ROUTE_ALL_METHODS] as $route) {
            if (Route::match($route->path, self::$url)) {
                return $route;
            }
        }

        return false;
    }

    public static function generateController($currentRoute)
    {
        if (!$currentRoute) {
//             Générer page 404
            Controller::error404();
            return;
        }

        if (!isset($currentRoute->controller) OR !isset($currentRoute->action)) {
            echo "The controller and/or action to call are not defined in routing.json";
            return;
        }

        $controllerName = "Controller\\" . $currentRoute->controller . "Controller";

        $controllerAction = $currentRoute->action . "Action";

        if (!class_exists($controllerName)) {
            Controller::error404();
            echo "Controller Class does not exist";
            return;
        }

        $controller = new $controllerName();


        if(!method_exists($controller, $controllerAction)) {
            Controller::error404();
            echo "Method does not exists";
            return;
        }

        return call_user_func_array(array($controller, $controllerAction), Route::$matches);

    }

}