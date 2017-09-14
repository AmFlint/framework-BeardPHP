<?php

namespace Helper;


use Exception\FileNotExist;

abstract class Controller
{
    /**
     * @var string - template engine used for rendering views
     */
    const TEMPLATE_ENGINE = '.html.twig';

    /**
     * @var \Twig_Environment self::$twig
     */
    protected static $twig;

    /**
     * Controller constructor.
     */
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

    /**
     * Initialize and set up Twig Environment
     */
    public static function init()
    {
        $loader = new \Twig_Loader_Filesystem(APP_VIEWS_DIR);
        $twig = new \Twig_Environment($loader, array(
            'cache' => false,
        ));
        self::$twig = $twig;
    }

    /**
     * Render a view file and pass parameters to be used into it.
     * @param $name - name of the file to open (without template engine)
     * @param array $params - parameters to be passed on to the view file under the form name => value
     */
    public static function render($name, $params = [])
    {
        $response = new Response();
        $response->buildViewFile($name)
            ->setParams($params)
            ->output();
    }
}