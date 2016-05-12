<?php

namespace Sixx\Router;

/**
 * Sixx\Router\RouteMapInterface
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

interface RouteMapInterface
{
    /**
     * Return all routes
     * @return array
     */
    public static function routes();

    /**
     * Set routes
     * @param array $array
     * @return null
     */
    public static function setRoutes(array $array);

    /**
     * Set route
     * @param array $array
     * @return null
     */
    public static function setRoute(array $array);

    /**
     * Get route by name
     * @param $name
     * @return array
     */
    public static function getRoute($name);
}
