<?php

namespace Helper;


abstract class Controller
{
    protected $model;
    protected static $twig;

    public function __construct()
    {
        self::init();
    }

    public static function error404()
    {
        self::init();
        header('HTTP/1.0 404 Not Found');
        echo self::$twig->render(
            "404.html.twig"
        );
    }

    public static function init()
    {
        $loader = new \Twig_Loader_Filesystem(APP_VIEWS_DIR);
        $twig = new \Twig_Environment($loader, array(
            'cache' => false,
        ));
        self::$twig = $twig;
    }
}