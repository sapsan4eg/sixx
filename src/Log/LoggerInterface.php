<?php

namespace Sixx\Log;

/**
 * Sixx\Log\LoggerInterface
 *
 * @package    Sixx
 * @subpackage Log
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */

interface LoggerInterface
{
    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     * @return mixed
     */
    public function alert($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     * @return mixed
     */
    public function notice($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = []);

    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = []);
}
