<?php

use Sixx\Router\RouteMapInterface;

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

    public static function setRoute(array $array)
    {
        self::$routes[$array['name']] = $array;
    }

    public static function getRoute($name)
    {
        return self::$routes[$name];
    }
}