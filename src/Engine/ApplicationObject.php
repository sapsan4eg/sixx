<?php

namespace Sixx\Engine;

/**
 * Sixx\Engine\ApplicationObject
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
abstract class ApplicationObject extends Object
{
    /**
     * Constructor
     */
    public function __construct($config = null)
    {
        $this->setUp($config);
        $this->afterConstruct();
    }

    protected function setUp($config)
    {
        $this->storage = new Storage();

        $this->config = new Config($config);

        require_once(\Sixx\Load\Loader::slash(__DIR__) . '../startup.php');

        \Sixx\Load\Loader::setDir($this->config->dir_base);

        if (! empty($this->config->dir_errors)) {
            \Sixx\Log\Logger::setDir(\Sixx\Load\Loader::slash($this->config->dir_base) . $this->config->dir_errors);
        }

        \Sixx\Load\Loader::setControllerDir($this->config->dir_controllers);

        if (! empty($this->config->entity)) {
            $entity = '\\' . ucfirst($this->config->entity) . 'Entity';
            if (! class_exists($entity))
                throw new \Sixx\Exceptions\NotfoundException('Cannot find entity');
        } elseif (class_exists('\\MysqlEntity')) {
            $entity = '\\MysqlEntity';
        }

        if (! empty($entity)) { #
            $this->entity = new $entity();
            if (! $this->entity instanceof \Sixx\Entity)
                throw new \Sixx\Exceptions\NotInstanceOfException('Entity must be instance of Sixx\\Entity');
        } else
            $this->entity = new EmptyEntity();
    }
}
