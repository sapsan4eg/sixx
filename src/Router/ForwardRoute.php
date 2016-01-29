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
     * @param array $get
     * @return null
     */
    protected function setUp($get = [])
    {
        if(! empty($get['controller']))
            $this->setController($get['controller']);

        if(! empty($get['action']))
            $this->setAction($get['action']);
    }
}
