<?php

namespace Sixx\Router;

/**
 * Sixx\Router\AbstractRoute
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
abstract class AbstractRoute implements RouteInterface
{
    protected $routes    = [[
        'name'             => 'default',
        'controller'       => 'home',
        'action'           => 'index',
        'url'              => '{controller}/{action}/',
        'error_controller' => 'error'
    ]];

    protected $route     = [];
    protected $uri       = [];
    protected $direction;
    protected $entity;
    protected $serverPath;
    protected $routeVar = '_route_';
    const REVERSE = 'reverse';
    const FORWARD = 'forward';

    /**
     * AbstractRoute constructor.
     * @param \Sixx\Net\Request $request
     * @param RouteMapInterface|null $map
     * @param EntityInterface|null $entity
     * @param null|string $routeVar
     */
    public function __construct(\Sixx\Net\Request $request, RouteMapInterface $map = null, EntityInterface $entity = null, $routeVar = null)
    {
        if (! empty($routeVar)) {
            $this->routeVar = $routeVar;
        }

        if (! empty($map)) {
            $this->routes = $map->routes();
        }

        $get = $request->get;
        $this->uri = $request->uri;
        $this->serverPath = $request->serverPath;

        if (! empty($entity)) {
            $this->entity = $entity;
        }

        $this->defaultRoute($this->routes[count($this->routes) - 1]);
        $this->setUp($get);

        unset($get['controller']);
        unset($get['action']);
        unset($get[$this->routeVar]);

        $this->setArguments($get);
    }

    /**
     * @param array|null $get
     * @return null
     */
    abstract protected function setUp(array $get = null);

    /**
     * @param string $name
     */
    protected function setController($name = '')
    {
        $this->route['controller'] = ucfirst(strtolower($name));
    }

    /**
     * @param string $name
     */
    protected function setAction($name = '')
    {
        $this->route['action'] = ucfirst(strtolower($name));
    }

    /**
     * @param array $arguments
     */
    public function setArguments($arguments = [])
    {
        $this->route['arguments'] = array_merge($this->route['arguments'], $arguments);
    }
    /**
     * create default route
     *
     * @access	protected
     * @param	array $route
     */
    protected function defaultRoute($route)
    {
        $this->route = [
            'controller' => ucfirst(strtolower($route['controller'])),
            'action' => ucfirst(strtolower($route['action'])),
            'default_controller' => ucfirst(strtolower($route['controller'])),
            'default_action' => ucfirst(strtolower($route['action'])),
            'error_controller' => ucfirst(strtolower($route['error_controller'])),
            'arguments' => [],
        ];
    }

    /**
     * @return array
     */
    public function route()
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function listRoutes()
    {
        return $this->routes;
    }

    public function direction()
    {
        return $this->direction;
    }

    public function uri()
    {
        return $this->uri;
    }

    public function serverPath()
    {
        return $this->serverPath;
    }
}
