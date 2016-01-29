<?php

namespace Sixx\Engine;

/**
 * Sixx\Engine\Object
 *
 * @package    Sixx
 * @subpackage Engine
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
abstract class Object
{
    protected $storage;

    /**
     * Object constructor.
     * @param $storage
     */
    public function __construct(Storage $storage = null)
    {
        $this->storage = $storage;
        $this->afterConstruct();
    }

    protected function afterConstruct()
    {

    }

    /**
     * @access public
     * @param string
     * @return mixed
     */
    public function __get($key)
    {
        return $this->storage->$key;
    }

    /**
     * @access public
     * @param string $key
     * @param mixed $value
     * @param null
     */
    public function __set($key, $value)
    {
        $this->storage->$key = $value;
    }

    /**
     * @access public
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->storage->$key);
    }
}
