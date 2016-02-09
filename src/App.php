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
    public function afterConstruct()
    {
        $this->request = new Net\Request((defined('HTTP_SERVER') ? HTTP_SERVER : ''));
        $this->response = new Net\Response();
        $this->model = new Engine\Model($this->storage);
        $this->getRouter();

        if ($this->entity->translate != false)
            Translate\Mui::start($this->entity->translate, $this->request, $this->config->default_language);

        if (! $this->autorizated())
            return null;

        $controllerName = '\\' . ucfirst(strtolower($this->router->route()['controller'])) . 'Controller';
        $action = ucfirst(strtolower($this->router->route()['action']));

        if (! class_exists($controllerName))
            return $this->notFound();

        $reflection = new \ReflectionClass($controllerName);

        if (! $reflection->hasMethod($action) || ! $reflection->getMethod($action)->isPublic())
            return $this->notFound();

        $controller = $reflection->newInstance($this->storage);

        if (! $controller instanceof Controller)
            throw new Exceptions\NotInstanceOfException('Controller ' . $controllerName . ' must be instance of \\Sixx\\Controller');

        $this->response->setContent($reflection->getMethod($action)->invokeArgs($controller, $this->arguments($reflection->getMethod($action))));
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
        if (! empty($this->config->autorization) && $this->entity->autorization != 'false') {
            if ($this->config->autorization !== 'true' && class_exists($this->config->autorization))
                $aut = $this->config->autorization;
            else
                $aut = '\\Sixx\\Autorization\\Simply';

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

    /**
     * @param \ReflectionMethod $action
     * @return array
     */
    protected function arguments(\ReflectionMethod $action)
    {
        $possible = array_merge($this->router->route()['arguments'], $this->request->post);
        $arguments = [];

        foreach ($action->getParameters() as $parameter) {
            if ($parameter->isOptional() != true && ! isset($possible[$parameter->getName()]))
                throw new \Sixx\Exceptions\NotHaveArgumentsException('Sorry but method ' . $action->getName() . ' have require parameters that you hasn\'t  transferred.');

            if (isset($possible[$parameter->getName()]))
                $arguments[] = $possible[$parameter->getName()];
        }

        return $arguments;
    }

    protected function notFound()
    {
        if (class_exists(ucfirst(strtolower($this->router->route()['error_controller'])) . 'Controller'))
            $this->response->setHeaders([
                'status' => 302,
                'Location' => $this->router->link($this->router->route()['default_action'], $this->router->route()['error_controller'])
            ]);
        else {
            $this->response->setHeaders(['status' => 404]);
            $this->response->setContent(file_get_contents(\Sixx\Load\Loader::slash(__DIR__) . 'Html/notfound.html'));
        }
        $this->response->response();

        return null;
    }
}
