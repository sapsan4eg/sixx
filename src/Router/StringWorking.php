<?php

namespace Sixx\Router;

/**
 * Sixx\Router\StringWorking
 *
 * @package    Sixx
 * @subpackage Router
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class StringWorking
{
    /**
     * @param string $uri
     * @return string
     */
    public static function clearUri($uri = '')
    {
        if(strlen($uri) == strrpos($uri, '/') + 1)
            $uri = substr($uri, 0, strlen($uri) -1);
        else
            $uri = str_replace('/index.php', '', $uri);

        return $uri;
    }

    /**
     * get name from path route
     *
     * @access public
     * @param string
     * @return string
     */
    public static function getName($value = '') {
        if(strpos($value, '{') === false && strpos($value, '}') === false)
            return '';

        return substr($value, strpos($value, '{') + 1, strlen($value) - (strlen($value) - strpos($value, '}')) - (strpos($value, '{') + 1));
    }

    /**
     * get value from path url
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	string
     */
    public static function clearRoute($reg, $value)
    {
        $value = substr($value, strpos($reg, '{'));

        return substr($value, 0, strlen($value) - (strlen($reg) - strpos($reg, '}') - 1));
    }

    /**
     * @access public
     * @param string $first
     * @param string $second
     * @return bool
     */
    public static function samePatterns($first, $second)
    {
        if(strpos($first, '{') !== false && strpos($first, '}') !== false) {
            $start = strpos($first, '{');
            $end = strlen($first) - strpos($first, '}') -1;

            if(substr($second, 0, $start) == substr($first, 0, $start) &&
                (substr($second, strlen($second) - $end) == substr($first, strlen($first) - $end))
            )
                return true;
        }

        return false;
    }

    /**
     * @access public
     * @param string $uri
     * @return array
     */
    public static function map($uri = '')
    {
        if(strpos($uri, '/') === 0)
            $uri = substr($uri, 1);

        if(strlen($uri) - 1 == strrpos($uri, '/'))
            $uri = substr($uri, 0, strrpos($uri, '/'));

        return explode('/', $uri);
    }

    /**
     * @param string $string
     * @return array
     */
    public static function arguments($string = '')
    {
        $arguments = [];

        $array = explode('&', htmlspecialchars_decode($string));

        foreach($array as $value) {
            if(strpos($value, '=') < 1 || strpos($value, '=') == strlen($value) - 1)
                continue;

            $name = substr($value, 0, strpos($value, '='));
            $val = substr($value, strpos($value, '=') + 1);

            #if($name != 'controller' & $name != 'action')
                $arguments[$name] = $val;
        }

        return $arguments;
    }

    /**
     * @param string $uri
     * @param array $arguments
     * @return bool
     */
    public static function itSameRoute($uri, $arguments = [])
    {
        $map = self::map($uri);

        foreach($map as $value) {
            if(strpos($value, '{') !== false) {
                $value = self::getName($value);

                if($value != 'controller' && $value != 'action' && $value != 'personal_route' && ! isset($arguments[$value]))
                    return false;
            }
        }

        return true;
    }
}