<?php

namespace Sixx\Router;

/**
 * Sixx\Router\RouteInterface
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

interface RouteInterface
{
    /**
     * @return array
     */
    public function route();

    /**
     * @return array
     */
    public function listRoutes();

    /**
     * @return string
     */
    public function direction();

    /**
     * @return array
     */
    public function uri();

    /**
     * @return string
     */
    public function serverPath();

    /**
     * @return string
     */
    public function requestUrl();
}
