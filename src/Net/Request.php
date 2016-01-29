<?php

namespace Sixx\Net;

/**
 * Sixx\Net\Request
 *
 * @package    Sixx
 * @subpackage Net
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class Request
{
    public $get     = [];
    public $post    = [];
    public $cookie  = [];
    public $files   = [];
    public $server  = [];
    public $headers = [];
    public $request = [];
    public $method  = [];
    public $url = '';
    public $uri = [];
    public $serverPath = '';

    /**
     * Constructor
     */
    public function __construct($server = '')
    {
        if (! session_id()) {
            ini_set('session.use_cookies', 'On');
            ini_set('session.use_trans_sid', 'Off');
            session_set_cookie_params(0, '/');
            session_start();
        }

        $this->killGlobals();
        $this->iisCompatibility();

        $_GET     = $this->clean($_GET);
        $_POST    = $this->clean($_POST);
        $_REQUEST = $this->clean($_REQUEST);
        $_COOKIE  = $this->clean($_COOKIE);
        $_FILES   = $this->clean($_FILES);
        $_SERVER  = $this->clean($_SERVER);
        $_SESSION = $this->clean($_SESSION);

        $this->get     = $_GET;
        $this->post    = $_POST;
        $this->request = $_REQUEST;
        $this->cookie  = $_COOKIE;
        $this->files   = $_FILES;
        $this->server  = $_SERVER;
        $this->session = new Session();
        $this->headers = $this->headers();
        $method = $this->method();
        $this->method = $method;

        if($this->method != 'GET' && $this->method != 'POST')
            $this->$method = $this->post;

        $this->url = 'http' . (isset($this->server['HTTPS']) ? 's' : '') . '://' . $this->server['HTTP_HOST'] . str_replace('&amp;', '&', urldecode($this->server['REQUEST_URI']));
        $this->uri = parse_url($this->url);
        $this->serverPath = $server;
    }

    /**
     * get array of headers from request
     *
     * @return  array
     */
    protected function headers()
    {
        $headers = [];
        foreach($this->server as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            } elseif(in_array($key, ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'])) {
                $headers[$key] = $value;
            }
        }

        if (isset($this->server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $this->server['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = isset($this->server['PHP_AUTH_PW']) ? $this->server['PHP_AUTH_PW'] : '';
            return $headers;
        }

        $authorizationHeader = null;

        if (! empty($this->server['HTTP_AUTHORIZATION']))
            $authorizationHeader = $this->server['HTTP_AUTHORIZATION'];
        elseif (! empty($this->server['REDIRECT_HTTP_AUTHORIZATION']))
            $authorizationHeader = $this->server['REDIRECT_HTTP_AUTHORIZATION'];

        if ($authorizationHeader === null)
            return $headers;

        if (0 === stripos($authorizationHeader, 'basic ')) {
            $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)), 2);
            if (count($exploded) == 2)
                list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
        } elseif (empty($this->server['PHP_AUTH_DIGEST']) && (stripos($authorizationHeader, 'digest ')) === 0) {
            $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
        } elseif (0 === stripos($authorizationHeader, 'bearer '))
            $headers['AUTHORIZATION'] = $authorizationHeader;

        return $headers;
    }

    /**
     * get method what was requested
     *
     * @return  string
     */
    protected function method()
    {
        $method = 'GET';
        if(! empty($this->server['REQUEST_METHOD']))
            $method = strtoupper($this->server['REQUEST_METHOD']);

        if($method == 'POST' && ! empty($this->headers['X-HTTP-METHOD-OVERRIDE']))
            $method = strtoupper($this->headers['X-HTTP-METHOD-OVERRIDE']);

        return $method;
    }

    /**
     * remove special chars
     *
     * @access public
     * @param array|string $data
     * @return array|string
     */
    public function clean($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $data[$this->clean($key)] = $this->clean($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_COMPAT);
        }

        return $data;
    }

    protected function iisCompatibility()
    {
        if (! isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME']))
            $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));

        if (! isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['PATH_TRANSLATED']))
            $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));

        if (! isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

            if (isset($_SERVER['QUERY_STRING']))
                $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }
    }

    protected function killGlobals()
    {
        if (ini_get('register_globals')) {
            $globals = array($_REQUEST, $_SESSION, $_SERVER, $_FILES);
            foreach ($globals as $global) {
                foreach(array_keys($global) as $key) {
                    unset(${$key});
                }
            }
        }
    }
}
