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
 */
abstract class ApplicationObject extends Object
{
    /**
     * Constructor
     */
    public function __construct($config = '')
    {
        $this->setUp($config);
        $this->afterConstruct();
    }

    protected function setUp($config = '')
    {
        $this->storage = new Storage();

        require_once(__DIR__ . '/../startup.php');

        $this->config = new Config($config);

        if (! empty($this->config->dir_errors)) {
            \Sixx\Log\Logger::setDir(\Sixx\Load\Loader::slash(DIR_BASE) . $this->config->dir_errors);
        }

        if (! empty($this->config->dirs)) {
            \Sixx\Load\Loader::setDirs($this->config->dirs);
        }

        if (! empty($this->config->entity)) {
            $entity = '\\' . ucfirst($this->config->entity) . 'Entity';
        } else {
            $entity = '\\MysqlEntity';
        }

        $this->entity = new $entity();
    }
}
