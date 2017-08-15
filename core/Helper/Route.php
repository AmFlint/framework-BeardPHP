<?php

namespace Helper;


class Route
{
    public static $path;
    public static $matches;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public static function match($path, $url)
    {
        self::$path = trim($path, "/");
        $url = trim($url, "/");

        $path = preg_replace('#:([\w]+)#', '([^/]+)', self::$path);
        $regex = "#^$path$#i";
        if(!preg_match($regex, $url, $matches)){
            return false;
        }
        array_shift($matches);
        self::$matches = $matches;  // On sauvegarde les paramètre dans l'instance pour plus tard
        return true;
    }

}