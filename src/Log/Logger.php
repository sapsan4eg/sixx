<?php

namespace Sixx\Log;

/**
 * Sixx\Log\Logger
 *
 * @package    Sixx
 * @subpackage Log
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 *
 * @method static null emergency($message, array $context = [])
 * @method static null alert($message, array $context = [])
 * @method static null critical($message, array $context = [])
 * @method static null error($message, array $context = [])
 * @method static null warning($message, array $context = [])
 * @method static null notice($message, array $context = [])
 * @method static null info($message, array $context = [])
 * @method static null debug($message, array $context = [])
 * @method static null log($level, $message, array $context = [])
 * @method static null setDir($dir)
 */
class Logger
{
    /**
     * @var AbstractLogger
     */
    protected static $log;

    /**
     * Logger constructor.
     */
    protected function __construct()
    {

    }

    /**
     * @param string $name
     * @param array $arguments
     * @return bool|null|void
     */
    public static function __callStatic($name, $arguments)
    {
        if (empty(self::$log)) {
            self::$log = new Log();
        }

        if (count($arguments) < 1) {
            return false;
        }

        if (count($arguments) == 3) {
            if ($name != 'log' || !is_string($arguments[0]) || !is_string($arguments[1]) || !is_array($arguments[2])) {
                return false;
            } else {
                return self::$log->log($arguments[0], $arguments[1], $arguments[2]);
            }
        }

        if (count($arguments) == 2) {
            if (is_string($arguments[0]) && is_string($arguments[1]) && $name == 'log') {
                return self::$log->log($arguments[0], $arguments[1]);
            } elseif (is_string($arguments[0]) || !is_array($arguments[1]) && $name != 'log') {
                return self::$log->$name($arguments[0], $arguments[1]);
            }
        }

        if ($name != 'log' && count($arguments) == 1 && is_string($arguments[0])) {
            return self::$log->$name($arguments[0]);
        }
    }
}
