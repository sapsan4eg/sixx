<?php

namespace Sixx\Engine;

/**
 * Sixx\Engine\App
 *
 * @package    Sixx
 * @subpackage Engine
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class App extends ApplicationObject
{
    /**
     * Constructor
     */
    public function afterConstruct()
    {
        if (class_exists('Bundles'))
            \Bundles::create();

        $this->request = new \Sixx\Net\Request((defined('HTTP_SERVER') ? HTTP_SERVER : ''));
        $this->response = new \Sixx\Net\Response();
        $this->session = $this->request->session;

        $map = null;

        if(! empty($this->config->routemap) && class_exists($this->config->routemap))
            $map = $this->config->routemap;

        if (!empty($this->request->get['_route_'])) {
            $router = new \Sixx\Router\ReverseRoute($this->request, new $map(), $this->entity->application);
        } else
            $router = new \Sixx\Router\ForwardRoute($this->request, new $map());

        if ($router->direction() == \Sixx\Router\AbstractRoute::$REVERSE)
            $this->router = new \Sixx\Router\ReverseLink($router, $this->entity->application);
        else
            $this->router = new \Sixx\Router\ForwardLink($router);

        \Sixx\Translate\Mui::start($this->entity->application, $this->request);

        if (! empty($this->config->autorization)) {
            if ($this->config->autorization !== true && class_exists($this->config->autorization))
                $aut = $this->config->autorization;
            else
                $aut = '\\Sixx\\Autorization\\Simply';

            $this->autorization = new $aut($this->entity->autorization, $this->router, $this->request);

            if (!$this->autorization->havePermission) {
                $this->response->setHeaders(['status' => 302, 'Location' => $this->autorization->link]);
                $this->response->response();
                return null;
            }
        }

        $controllerName =  '\\' . ucfirst(strtolower($router->route()['controller'])) . 'Controller';
        $action = $router->route()['action'];
        $arguments = [];
        $this->model = new Model($this->storage);

        if (! class_exists($controllerName)
            || ! ($controller = new $controllerName($this->storage))
            || ! method_exists($controller, $action)
            || array_search($action, get_class_methods($controller)) === false
        ) {
            $this->response->setHeaders([
                'status' => 302,
                'Location' => $this->router->link($router->route()['default_action'], $router->route()['error_controller'])
            ]);
            $this->response->response();

            return null;
        }

        $return = call_user_func_array([$controller, $action], $arguments);

        $this->response->setContent($return);
        $this->response->response();
        $this->entity->analytic->execution_time();

        return null;
    }
}
