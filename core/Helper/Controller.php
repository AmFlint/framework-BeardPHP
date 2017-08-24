<?php

namespace Helper;


use Exception\FileNotExist;

abstract class Controller
{
    const TEMPLATE_ENGINE = '.html.twig';
    protected $model;
    /* @var \Twig_Environment self::$twig*/
    protected static $twig;

    public function __construct()
    {
        self::init();
    }

    public static function error404()
    {
        self::init();
        header('HTTP/1.0 404 Not Found');
        self::render('errors/404');
    }

    public static function init()
    {
        $loader = new \Twig_Loader_Filesystem(APP_VIEWS_DIR);
        $twig = new \Twig_Environment($loader, array(
            'cache' => false,
        ));
        self::$twig = $twig;
    }

    public static function render($name, $params = [])
    {
        $response = new Response();
        return $response
            ->buildViewFile($name)
            ->setParams($params)
            ->output();
    }
}