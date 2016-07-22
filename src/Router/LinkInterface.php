<?php

namespace Sixx\Router;

interface LinkInterface
{
    /**
     * Create Link class
     * @param RouteInterface $router
     * @param EntityInterface|null $entity
     */
    public function __construct(RouteInterface $router, EntityInterface $entity = null);

    /**
     * Manipulation with route
     * @return RouteInterface
     */
    public function route();

    /**
     * Create url to action
     * @param string $action
     * @param string $controller
     * @param array $arguments
     * @return mixed
     */
    public function link($action = '', $controller = '', $arguments = []);

    /**
     * Create url to file
     * @param array $server
     * @param string $serverPath
     * @param string $name
     * @return mixed
     */
    public static function source(array $server, $serverPath = '', $name = '');

    /**
     * Explain what direction we use
     * @return mixed
     */
    public function direction();

    /**
     * Return requested uri
     * @return array
     */
    public function uri();

    /**
     * Return url requested
     * @return string
     */
    public function requestUri();
}
