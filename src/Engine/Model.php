<?php

namespace Sixx\Engine;

/**
 * Sixx\Engine\Model
 *
 * @package    Sixx
 * @subpackage Engine
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class Model extends Object
{
    /**
     * @param string $name
     * @param array $arguments
     * @param bool $alternativeName
     * @throws \Exception
     */
    public function join($name, $arguments = [], $alternativeName = false)
    {
        $name = str_replace('/', '\\', $name);

        if ($alternativeName === false )
            $alternativeName = str_replace(['\\', '/'], '', $name ) . 'Model';

        if (strpos($name,'\\') !== 0)
           $name  = '\\' . $name;

        if (! class_exists($name))
            throw new \Sixx\Exceptions\NotfoundException('Class ' . $name . ' cannot be found in this project.');

        if (get_parent_class($name) == 'Sixx\Engine\Object')
            array_unshift($arguments, $this->storage);

        $refClass = new \ReflectionClass($name);
        $this->$alternativeName = $refClass->newInstanceArgs($arguments);

        if (! $this->$alternativeName instanceof \Sixx\Model)
            throw new \Sixx\Exceptions\NotInstanceOfException('Class ' . $name . ' must be instance of \\Sixx\\Model');
    }
}
