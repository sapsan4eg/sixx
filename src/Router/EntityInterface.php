<?php

namespace Sixx\Router;

/**
 * Sixx\Router\EntityInterface
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

interface EntityInterface
{
    /**
     * @param string $keyword
     * @param string $route
     * @return bool
     */
    public function setRoute($keyword, $route);

    /**
     * @param string $keyword
     * @return bool|string
     */
    public function getRoute($keyword);

    /**
     * @return bool|array
     */
    public function listRoutes();
}
