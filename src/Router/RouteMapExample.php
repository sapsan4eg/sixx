<?php

namespace Sixx\Router;

/**
 * Sixx\Router\ForwardRoute
 *
 * @package    Sixx
 * @subpackage Router
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class RouteMapExample implements RouteMapInterface
{
    private static $routes;

    public static function routes()
    {
        return self::$routes;
    }

    public static function setRoutes(array $array)
    {
        self::$routes = $array;
    }
}