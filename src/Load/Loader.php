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
    protected static $listDir;
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

        foreach (self::$listDir as $dir) {
            $file = $dir . $name . '.php';

            if (file_exists($file)) {
                require_once($file);
                break;
            } elseif (file_exists(($file = $dir . $name . '.php'))) {
                require_once($file);
                break;
            }
        }
    }

    public static function setDirs($dirs)
    {
        if (is_string($dirs))
            self::setDir($dirs);
        else if(is_array($dirs)) {
            foreach($dirs as $dir) {
                self::setDir($dir);
            }
        }
    }

    protected static function setDir($dir)
    {
        self::$listDir[] = $dir;
    }

    public static function slash($string = '')
    {
        if(strrpos($string, '/') != strlen($string))
            $string .= '/';

        return $string;
    }
}
