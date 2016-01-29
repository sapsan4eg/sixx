<?php

use Sixx\Log;

/**
 * Six-X
 *
 * An open source application development framework for PHP 5.5.0 or newer
 * system Initialization File
 *
 * @package
 * @author		Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright	Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license		http://six-x.org/guide/license.html
 * @link		http://six-x.org
 * @since		Version 1.0.0.0
 * @filesource
 */

/**
 * Determines if the current version of PHP is greater then the supplied value
 *
 * @access	public
 * @param	string
 * @return	bool	TRUE if the current version is $version or higher
 */
if (version_compare(phpversion(), '5.5.0', '<') == true)
    exit('PHP5.5+ Required');

/**
 * ------------------------------------------------------
 *  Set default time zone
 * ------------------------------------------------------
 */
if (! ini_get('date.timezone'))
    date_default_timezone_set('Asia/Yekaterinburg');

/**
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
error_reporting(E_ALL);
ini_set('html_errors', 'on');

/**
 * ------------------------------------------------------
 *  Define a custom error handler so we can log PHP errors
 * ------------------------------------------------------
 */
set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');

/**
 * Error Handler
 * This function lets us invoke the exception class and
 * display errors using the standard error template located
 * This function will send the error page directly to the
 * browser and exit.
 *
 * @access	public
 * @return	void
 */
function errorHandler($errno, $message, $file, $line)
{
    switch ($errno) {
        case E_NOTICE:
        case E_USER_NOTICE:
            $error = Log\LogLevel::NOTICE;
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $error = Log\LogLevel::WARNING;
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $error = Log\LogLevel::CRITICAL;
            break;
        default:
            $error = Log\LogLevel::ALERT;
            break;
    }

    $program = str_replace(\Sixx\Load\Loader::getDir(), '', dirname($file));

    Log\Logger::$error($message . ' File: ' . $file . ' Line: ' . $line, ['PROGRAM' => $program]);
    writeError($error, $message . ' File: ' . $file . ' Line: ' . $line);
}

/**
 * Write exception on display or log file
 * This function lets us invoke the exception class and
 * display errors using the standard error template located
 * This function will send the error page directly to the
 * browser and exit.
 *
 * @access public
 * @param Exception $exception
 * @return null
 */
function exceptionHandler(\Exception $exception)
{
    $message = $exception->getMessage() . ' File: ' . $exception->getFile() . ' Trace: ' . $exception->getTraceAsString();
    Log\Logger::error($message, ['PROGRAM' => get_class($exception)]);
    writeError(Log\LogLevel::ERROR, $message);
}

/**
 * Write error on display or log file
 * This function lets us invoke the exception class and
 * display errors using the standard error template located
 * This function will send the error page directly to the
 * browser and exit.
 *
 * @access public
 * @param string $error
 * @param string $message
 * @return null
 */
function writeError($error, $message)
{
    if(defined('ERR_DISPLAY') && ERR_DISPLAY)
        echo '<i><b>' . strtoupper($error) . '</b>: ' . $message . '</i><br />';
}

/**
 * Write fatal error on display or log file
 * This function lets us invoke the exception class and
 * display errors using the standard error template located
 * This function will send the error page directly to the
 * browser and exit.
 *
 * @access public
 * @param string $buffer
 * @return string
 */
function fatalErrorHandler($buffer) {
    if (preg_match("|(Fatal error</b>:)(.+)(<br)|", $buffer, $regs)) {
        Log\Logger::critical($buffer, ['PROGRAM' => 'cron/fatal']);
        return "FATAL ERROR CAUGHT, check log file" ;
    }
    return $buffer;
}

if (! empty($argv))
    define('ARGV', implode('|', $argv));

/**
 * ------------------------------------------------------
 *  Enable application autoload
 * ------------------------------------------------------
 */
spl_autoload_register(
    function($name) {
        \Sixx\Load\Loader::autoload($name);
    }
);
