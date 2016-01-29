<?php

namespace Sixx\Engine;

/**
 * Sixx\Engine\Storage
 *
 * @package    Sixx
 * @subpackage Net
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class Storage
{

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}