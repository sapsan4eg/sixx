<?php

namespace Sixx\Router;

/**
 * Sixx\Router\ForwardRoute
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
class ForwardRoute extends AbstractRoute
{
    /**
     * @param array|null $get
     * @return null
     */
    protected function setUp(array $get = null)
    {
        $this->direction = self::FORWARD;

        if (! empty($get['controller'])) {
            $this->setController($get['controller']);
        }

        if (! empty($get['action'])) {
            $this->setAction($get['action']);
        }
    }
}
