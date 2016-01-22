<?php

namespace Sixx\Log;

/**
 * Sixx\Log\AbstractLogger
 *
 * @package    Sixx
 * @subpackage Log
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */

abstract class AbstractLogger implements LoggerInterface
{
    protected $dir;
    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return null
     */
    abstract public function log($level, $message, array $context = []);

    public function setDir($dir)
    {
        $this->dir = $dir;
    }
}
