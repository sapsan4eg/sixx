<?php

namespace Sixx\Router;

/**
 * Sixx\Router\AbstractRoute
 *
 * @package    Sixx
 * @subpackage Router
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
abstract class AbstractRoute implements RouteInterface
{
    protected $routes    = [[
        'name'             => 'default',
        'controller'       => 'Home',
        'action'           => 'Index',
        'url'              => '{controller}/{action}/',
        'error_controller' => 'Error'
    ]];
    protected $route     = [];
    protected $uri       = [];
    protected $direction;
    protected $entity;
    protected $serverPath;
    public static $REVERSE = 'reverse';
    public static $FORWARD = 'forward';

    /**
     * Route constructor.
     * @param \Sixx\Net\Request $request
     * @param RouteMapInterface $map
     * @param EntityInterface $entity
     */
    public function __construct(\Sixx\Net\Request $request, RouteMapInterface $map = null, EntityInterface $entity = null)
    {
        $this->direction = self::$FORWARD;

        if (! empty($map))
            $this->routes = $map->routes();

        $get = $request->get;
        $this->uri = $request->uri;
        $this->serverPath = $request->serverPath;

        if(! empty($entity))
            $this->entity = $entity;

        $this->defaultRoute($this->routes[count($this->routes) - 1]);

        $this->setUp($get);

        unset($get['controller']);
        unset($get['action']);
        unset($get['_route_']);

        $this->setArguments($get);

        if(defined('DIRECTION_LINKS') AND strtolower(DIRECTION_LINKS) == self::$REVERSE)
            $this->direction = self::$REVERSE;
    }

    /**
     * @param array $get
     * @return null
     */
    abstract protected function setUp($get = []);

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
