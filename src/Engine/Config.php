<?php

namespace Sixx\Engine;

use Sixx\Exceptions\NotfoundException;

/**
 * Sixx\Engine\Config
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
class Config
{
    /**
     * Config constructor.
     * @param string|array $config
     */
    public function __construct($config)
    {
        if (defined('DIR_BASE'))
            $this->dir_base = DIR_BASE;

        if (is_string($config))
            $this->fillByFile($config);
        elseif (is_array($config))
            $this->fillByArray($config);

        if (empty($this->dir_base) || ! file_exists($this->dir_base) || ! is_dir($this->dir_base))
            throw new NotfoundException('Cannot find your base dir, please add real basedir like dir_base=/var/www/yourproject/ in your configuration file.');

        if (empty($this->dir_controllers))
            $this->dir_controllers = \Sixx\Load\Loader::slash(\Sixx\Load\Loader::slash($this->dir_base) . 'controllers');
        else
            $this->dir_controllers = \Sixx\Load\Loader::slash(\Sixx\Load\Loader::slash($this->dir_base) . $this->dir_controllers);

        if (empty($this->dir_shared))
            $this->dir_shared = 'shared';
    }

    /**
     * @param string $name
     * @return null|object
     */
    public function __get($name)
    {
        if (! isset($this->$name))
            return null;
        else
            return $this->$name;
    }

    /**
     * @param string $string
     */
    protected function fillByFile($string)
    {
        if (file_exists($string)) {
            $content = explode(PHP_EOL, file_get_contents($string));

            foreach ($content as $value) {
                if (strpos($value, '=') > 0) {
                    $value = str_replace(['"', "'"], '', $value);
                    $name = strtolower(trim(substr($value, 0 , strpos($value, '='))));
                    $this->$name = trim(substr($value, strpos($value, '=') + 1));
                }
            }
        }
    }

    /**
     * @param array $array
     */
    protected function fillByArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (! is_numeric($key)) {
                $key = strtolower(trim($key));
                $this->$key = $value;
            }
        }
    }
}
