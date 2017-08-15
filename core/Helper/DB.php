<?php
/**
 * Created by PhpStorm.
 * User: antoinemasselot
 * Date: 24/05/2017
 * Time: 10:27
 */

namespace Helper;

use PDO;

class DB
{
    public static $db = null;

    public static function get()
    {
        if (is_null(self::$db)) {
            try {
                self::$db = new PDO(DBN,DB_USER,DB_PWD);
            } catch(\PDOException $exception) {
                die($exception->getMessage());
            }
            self::$db->exec("SET NAMES UTF8");
        }

        return self::$db;
    }
}