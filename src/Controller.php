<?php

namespace Sixx;

use Sixx\Net\Request;
use Sixx\Authorization\AuthorizationInterface;

class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    public function __construct(Request $request, View $view, Router $router, AuthorizationInterface $authorization = null)
    {
        $this->request = $request;
        $this->view = $view;
        $this->router = $router;
        $this->authorization = $authorization;
        $this->view->setAction($this->router->getAction());
        $this->view->setController($this->router->getController());
    }
}
