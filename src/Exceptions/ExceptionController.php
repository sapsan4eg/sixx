<?php

namespace Sixx\Exceptions;

use Sixx\Controller;

/**
 * Class ExceptionController
 * @package Sixx\Exceptions
 */
class ExceptionController extends Controller
{
    /**
     * @param int $status
     * @param string $message
     * @return $view
     */
    public function Error($status, $message = '')
    {
        if (method_exists("\\Controllers\\" . $this->router->getErrorController() . "Controller", $this->router->getDefaultAction())) {
            $this->request->session['error'] = [$status, $message];
            return $this->view->redirectToAction($this->router->getDefaultAction(), $this->router->getErrorController());
        }

        $file = __DIR__ . '/../Html/' . $status . '.html';

        if (!file_exists($file)) {
            $file = __DIR__ . '/../Html/error.html';
        }

        $this->view->code = $status;
        $this->view->message = $message;
        $this->view->setStatus($status);

        return $this->view->contentResult($file);
    }

    /**
     * @param $status
     * @param string $message
     * @return $views
     */
    public function JsonError($status, $message = '')
    {
        $this->view->setStatus($status);
        return $this->view->jsonResult($message);
    }

    public function RedirectError($controller, $action, $arguments)
    {
        return $this->view->redirectToAction($action, $controller, $arguments);
    }
}
