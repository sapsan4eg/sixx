<?php

namespace Sixx\Log;

/**
 * Sixx\Log\Log
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

class Log extends AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        if( ! defined('Sixx\Log\LogLevel::' . strtoupper($level)))
            return false;

        if(empty($context['PROGRAM']))
            $context['PROGRAM'] = 'unknow';

        $context['MESSAGE'] = $message;

        $exception = array_merge(["LEVEL_NAME" => $level], $context, self::getStat());

        $this->write(json_encode($exception), $context['PROGRAM']);
    }

    /**
     * Get data from environment
     *
     * @return array
     */
    protected function getStat()
    {
        $time = time();

        if( ! empty($_SERVER['REQUEST_METHOD'])) {
            $event["HTTP"] = ["SERVER" => [
                'HTTP_USER_AGENT'      => ! empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'HTTP_REFERER'         => ! empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                'HTTP_ACCEPT_ENCODING' => ! empty($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '',
                'HTTP_ACCEPT_LANGUAGE' => ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
                'REMOTE_ADDR'          => ! empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                'REQUEST_SCHEME'       => ! empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : '',
                'REMOTE_PORT'          => ! empty($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : '',
                'SERVER_PROTOCOL'      => ! empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : '',
                'REQUEST_METHOD'       => ! empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
                'QUERY_STRING'         => ! empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '']
                , ($_SERVER['REQUEST_METHOD'] == "POST" ? "POST" : "GET") =>
                    $_SERVER['REQUEST_METHOD'] == "POST" ? $_POST : $_GET,
                "COOKIE"               => $_COOKIE,
            ];
        } else {
            $event["CRON"] = ["CRON" => "TRUE", "ARGV" => defined('ARGV') ? ARGV : "FALSE"];
        }

        $event["TIME"] = ["EVENTIME" => date("c", $time), "UNIXTIME" => $time];
        $event["SOURCEIP"] = ! empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        $event["HOST"] = ! empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';

        return $event;
    }

    /**
     * @param string $path
     * @param null|string $name
     * @return bool|string
     */
    protected function fileName($path = '', $name = null)
    {
        if (! empty($this->dir))
            $dir = \Sixx\Load\Loader::slash($this->dir);
        else
            $dir = \Sixx\Load\Loader::slash(\Sixx\Load\Loader::getDir()) . 'logs/';

        $dir .= strtolower(str_replace([\Sixx\Load\Loader::getDir(), 'vendor/', 'src/'], '', str_replace('\\', '/', $path)));

        if (! file_exists($dir) && ! mkdir($dir, 0777, true))
                return false;

        return $dir . ($name === null ?  date("Y-m-d") : $name) . '.log';
    }

    /**
     * @param string $message
     * @param string $path
     */
    protected function write($message, $path = '')
    {
        $filename = $this->fileName($path . '/', date("Y-m-d"));

        if($filename !== false) {
            $handle = fopen($filename, 'a+');
            fwrite($handle, $message . PHP_EOL);
            fclose($handle);
        }
    }
}
