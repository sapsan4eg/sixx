<?php

namespace Sixx\Load;

/**
 * Sixx\Load\Loader
 *
 * @package    Sixx
 * @subpackage Load
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class Loader
{
    protected static $listDir = [];
    protected static $controllerDir;
    protected static $dirBase;

    /**
     * @param $name
     * @param $dir
     * @param string $ext
     */
    public static function load($name, $dir, $ext = 'php')
    {
        $file = $dir . '/' . $name . '.' . $ext;
        if (file_exists($file))
            require_once($file);
    }

    /**
     * @param $name
     */
    public static function autoload($name)
    {
        $name = str_replace('\\', '/', $name);

        if (strpos($name, '/') === 0)
            $name = substr($name, 1);

        if (strripos($name, 'controller') == strlen($name) - 10) {
            if (file_exists(self::$controllerDir . $name . '.php'))
               require_once(self::$controllerDir . $name . '.php');
            return;
        }

        if (empty(self::$dirBase))
            return;

        if (file_exists(self::$dirBase . $name . '.php')) {
            require_once(self::$dirBase . $name . '.php');
        }
    }

    public static function setDir($dir = '')
    {
        if (! empty($dir) && file_exists($dir))
            self::$dirBase = $dir;
    }

    public static function getDir()
    {
        return self::$dirBase;
    }

    public static function slash($string = '')
    {
        if(strrpos($string, '/') != strlen($string) -1)
            $string .= '/';

        return $string;
    }

    public static function setControllerDir($dir = '')
    {
        self::$controllerDir = self::slash($dir);
    }
}
