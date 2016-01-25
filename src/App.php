<?php

namespace Sixx;

/**
 * Sixx\App
 *
 * @package    Sixx
 * @subpackage
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0
 *
 * @property \Sixx\Net\Request $request
 * @property \Sixx\Net\Response $response
 * @property \Sixx\Engine\Model $model
 * @property \Sixx\Router\AbstractLink $router
 * @property \Sixx\Autorization\AutorizationInterface $autorization
 */
class App extends Engine\ApplicationObject
{
    /**
     * Constructor
     */
    public function afterConstruct()
    {
        $this->request = new Net\Request((defined('HTTP_SERVER') ? HTTP_SERVER : ''));
        $this->response = new Net\Response();
        $this->model = new Engine\Model($this->storage);
        $this->getRouter();

        if ($this->entity->translate != false)
            Translate\Mui::start($this->entity->translate, $this->request);

        if (! $this->autorizated())
            return null;

        $controllerName = '\\' . ucfirst(strtolower($this->router->route['controller'])) . 'Controller';
        $action = $this->router->route['action'];
        $arguments = [];

        if (! class_exists($controllerName)
            || ! ($controller = new $controllerName($this->storage))
            || ! method_exists($controller, $action)
            || array_search($action, get_class_methods($controller)) === false
        ) {
            if (class_exists(ucfirst(strtolower($this->router->route['error_controller'])) . 'Controller'))
                $this->response->setHeaders([
                    'status' => 302,
                    'Location' => $this->router->link($this->router->route['default_action'], $this->router->route['error_controller'])
                ]);
            else {
                $this->response->setHeaders(['status' => 404]);
                $this->response->setContent(file_get_contents(\Sixx\Load\Loader::slash(__DIR__) . 'Html/notfound.html'));
            }
            $this->response->response();

            return null;
        }

        if (! $controller instanceof Controller)
            throw new Exceptions\NotInstanceOfException('Controller ' . $controllerName . ' must be instance of \\Sixx\\Controller');

        $return = call_user_func_array([$controller, $action], $arguments);

        $this->response->setContent($return);
        $this->response->response();

        return null;
    }

    protected function getRouter()
    {
        $map = null;

        if (! empty($this->config->routemap) && class_exists($this->config->routemap)) {
            $map = '\\' . $this->config->routemap;
            $map = new $map();
        }

        if ($this->entity->router != false)
            $entity = $this->entity->router;
        else
            $entity = null;

        if (! empty($this->request->get['_route_'])) {
            $router = new Router\ReverseRoute($this->request, $map, $entity);
        } else
            $router = new Router\ForwardRoute($this->request, $map, null, $this->config->direction);

        if ($router->direction() == Router\AbstractRoute::$REVERSE)
            $this->router = new Router\ReverseLink($router, $entity);
        else
            $this->router = new Router\ForwardLink($router);
    }

    protected function autorizated()
    {
        if (! empty($this->config->autorization) && $this->entity->autorization != false) {
            if ($this->config->autorization !== true && class_exists($this->config->autorization))
                $aut = $this->config->autorization;
            else
                $aut = 'Autorization\\Simply';

            $this->autorization = new $aut($this->entity->autorization, $this->router, $this->request);

            if(! $this->autorization instanceof Autorization\AutorizationInterface)
                throw new Exceptions\NotInstanceOfException('Autorization class must implement \\Sixx\\Autorization\\AutorizationInterface');

            if (! $this->autorization->havesPermission()) {
                $this->response->setHeaders(['status' => 302, 'Location' => $this->autorization->link()]);
                $this->response->response();
                return false;
            }
        }

        return true;
    }
}
