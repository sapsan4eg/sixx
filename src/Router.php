<?php

namespace Sixx;

use Sixx\Net\Request;
use Sixx\Router\RouteMapInterface;
use Sixx\Router\EntityInterface;

class Router
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Router\LinkInterface
     */
    protected $router;

    /**
     * Router constructor.
     * @param Request $request
     * @param RouteMapInterface|null $routeMap
     * @param EntityInterface|null $entity
     * @param string $routeVar
     */
    public function __construct(Request $request, RouteMapInterface $routeMap = null, EntityInterface $entity = null, $routeVar = '_route_')
    {
        $this->request = $request;

        if (!empty($this->request->get[$routeVar])) {
            $name = 'Sixx\Router\ReverseRoute';
        } else {
            $name = 'Sixx\Router\ForwardRoute';
        }

        $this->router = (new $name($this->request, $routeMap, $entity, $routeVar))->getLink();
    }

    /**
     * Action name
     * @return string
     */
    public function getAction()
    {
        return $this->router->route()["action"];
    }

    /**
     * Controller name
     * @return string
     */
    public function getController()
    {
        return $this->router->route()["controller"];
    }

    /**
     * Error controller name
     * @return string
     */
    public function getErrorController()
    {
        return $this->router->route()["error_controller"];
    }

    public function getDefaultAction()
    {
        return $this->router->route()["default_action"];
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function getArguments()
    {
        return $this->router->route()["arguments"];
    }

    /**
     * Create url to action
     * @param string $action
     * @param string $controller
     * @param array $arguments
     * @return string
     */
    public function link($action = '', $controller = '', $arguments = [])
    {
        return $this->router->link($action, $controller, $arguments);
    }

    /**
     * Requested uri
     * @return array
     */
    public function uri()
    {
        return $this->router->uri();
    }

    /**
     * Requested url
     * @return string
     */
    public function requestUri()
    {
        return $this->router->requestUri();
    }

    /**
     * Direction of route
     * @return string
     */
    public function getDirection()
    {
        return $this->router->direction();
    }
}
