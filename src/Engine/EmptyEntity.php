<?php

namespace Sixx\Engine;

/**
 * Sixx\Engine\EmptyEntity
 *
 * @package    Sixx
 * @subpackage Engine
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 *
 * @property \Sixx\Entity $entity
 * @property \Sixx\Engine\Config $config
 */
class EmptyEntity extends \Sixx\Entity
{
    public function __get($name)
    {
        return false;
    }
}