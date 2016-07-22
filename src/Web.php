<?php

namespace Sixx;

use Sixx\Exceptions\ExceptionCatcher;
use Sixx\Router;
use Sixx\Net\Response;
use Sixx\DependencyInjection\Inject;

class Web
{
    public function __construct(ExceptionCatcher $exception, Router $router, Response $response)
    {
        $view = $this->getView($exception, $router);

        if (null != $view) {
            if (!empty($view->getHeaders())) {
                $response->setHeaders($view->getHeaders());
            }
            if (!empty($view->getContent())) {
                $response->setContent($view->getContent());
            }
        }

        $response->response();
    }

    /**
     * @param ExceptionCatcher $exception
     * @param Router $router
     * @return View
     */
    protected function getView(ExceptionCatcher $exception, Router $router)
    {
        try {
            $view = Inject::method(
                "Controllers\\" . ucfirst($router->getController()) . "Controller",
                $router->getAction(),
                array_merge($router->getRequest()->get, $router->getRequest()->post)
            );
        } catch (\Exception $e) {
            $view = $exception->showException($e);
        }

        return $view;
    }
}
