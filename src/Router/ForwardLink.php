<?php

namespace Sixx\Router;

/**
 * Sixx\Router\ForwardLink
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
class ForwardLink extends AbstractLink
{

    /**
     * @param string $url
     * @param string $controller
     * @param string $action
     * @param array $arguments
     * @return string
     */
    protected function fillUri($url = '', $controller = '', $action = '', $arguments = [])
    {
        return ['uri' => $url . '?controller=' . $controller . '&action=' . $action, 'arguments' => $arguments];
    }
}
