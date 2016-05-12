<?php

namespace Sixx;

use Sixx\Net\Request;

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

    public function __construct(Request $request, View $view, Router $router)
    {
        $this->request = $request;
        $this->view = $view;
        $this->router = $router;
        $this->view->setAction($this->router->getAction());
        $this->view->setController($this->router->getController());
    }
}
