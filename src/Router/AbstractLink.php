<?php

namespace Sixx\Router;

/**
 * Sixx\Router\AbstractLink
 *
 * @package    Sixx
 * @subpackage Router
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
abstract class AbstractLink implements LinkInterface
{
    protected $route;
    protected $uri;
    protected $serverPath;
    protected $entity;
    protected $routes;
    protected $direction;
    protected $requestUri;

    /**
     * @inheritdoc
     * @param RouteInterface $router
     * @param EntityInterface|null $entity
     */
    public function __construct(RouteInterface $router, EntityInterface $entity = null)
    {
        $this->route = $router->route();
        $this->uri = $router->uri();
        $this->serverPath = $router->serverPath();
        $this->entity = $entity;
        $this->routes = $router->listRoutes();
        $this->direction = $router->direction();
        $this->requestUri = $router->requestUrl();
    }

    /**
     * @return array
     */
    public function route()
    {
        return $this->route;
    }

    /**
     * @inheritdoc
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * @inheritdoc
     */
    public function requestUri()
    {
        return $this->requestUri;
    }

    /**
     * create url
     *
     * @param string $action
     * @param string $controller
     * @param array $arguments
     * @return string
     */
    public function link($action = '', $controller = '', $arguments = [])
    {
        if (empty($controller)) {
            $controller = isset($this->route['controller']) ? $this->route['controller'] : $this->route['default_controller'];
        }

        if (empty($action)) {
            $action = $this->route['default_action'];
        }

        $uri = $this->uri['scheme'] . '://' . $this->uri['host'] . (isset($this->uri['port']) ? ':' . $this->uri['port'] : '');

        $path = substr($this->serverPath, strpos($this->serverPath, '//') + 2);

        strpos($path, '/') > 0 ? ($path = substr($path, strpos($path, '/') + 1)) : ($path = '');

        $uri .= '/' . $path;
        $uri = (strlen($uri) - 1) == strrpos($uri, '/') ? $uri : ($uri . '/');

        $temp = $this->fillUri($uri, $controller, $action, $arguments);

        $uri = $temp['uri'];
        $arguments = $temp['arguments'];

        unset($temp);

        $uri = strpos($uri, '?') > 0 ? $uri . '&' : $uri . '?';

        foreach ($arguments as $key => $value) {
            $uri .= $key . '=' . urlencode($value) . '&';
        }

        $uri = substr($uri, 0, strlen($uri) - 1);

        return $uri;
    }

    /**
     * @param string $uri
     * @param string $action
     * @param string $controller
     * @return string
     */
    abstract protected function fillUri($uri = '', $controller = '', $action = '', $arguments);

    /**
     * @param array $server
     * @param string $serverPath
     * @param string $name
     * @return string
     */
    public static function source(array $server, $serverPath = '', $name = '')
    {
        $url = 'http' . (isset($server['HTTPS']) ? 's' : '') . '://' . $server['HTTP_HOST'];

        $path = substr($serverPath, strpos($serverPath, '//') + 2);

        strpos($path, '/') > 0 ? $path = substr($path, strpos($path, '/') + 1) : $path = '';

        $url .= '/' . $path;
        $url = ((strlen($url) - 1) == strrpos($url, '/') ? $url : $url . '/') . $name;

        return $url;
    }

    /**
     * @return string
     */
    public function direction()
    {
        return $this->direction;
    }
}
