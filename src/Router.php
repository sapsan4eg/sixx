<?php

namespace Sixx;

use Sixx\Net\Request;
use Sixx\Router\RouteMapInterface;
use Sixx\Router\EntityInterface;
use Sixx\Router\AbstractRoute;

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
     * @param null $routeVar
     */
    public function __construct(Request $request, RouteMapInterface $routeMap = null, EntityInterface $entity = null, $routeVar = null)
    {
        $this->request = $request;

        if (empty($routeVar)) {
            $routeVar = '_route_';
        }

        if (! empty($this->request->get[$routeVar])) {
            $name = 'Sixx\Router\ReverseRoute';
            $direction = AbstractRoute::REVERSE;
        } else {
            $name = 'Sixx\Router\ForwardRoute';
            $direction = AbstractRoute::FORWARD;
        }

        $router = new $name($this->request, $routeMap, $entity, $direction, $routeVar);

        if ($direction == AbstractRoute::REVERSE) {
            $this->router = new Router\ReverseLink($router);
        } else {
            $this->router = new Router\ForwardLink($router);
        }
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

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
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
}
