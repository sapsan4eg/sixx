<?php

namespace Sixx;

use Sixx\Router;
use Sixx\Net\Response;
use Sixx\DependencyInjection\Exceptions\InjectException;
use Sixx\DependencyInjection\Exceptions\InjectRequiredParameterException;
use Sixx\Log\LoggerInterface;
use Sixx\DependencyInjection\Inject;

class Web
{
    public function __construct(Router $router, Response $response, LoggerInterface $log)
    {
        $view = null;

        try {
            $view = $this->view($router);
        } catch (InjectRequiredParameterException $e) {
            $log->error($e->getMessage(), ["PROGRAM" => "web/" . $router->getController() . "Controller"]);

            if (!$this->redirectError($response, $router, "Error")) {
                $this->showError(500, $response);
            }

        } catch (InjectException $e) {
            if (!$this->redirectError($response, $router, "Notfound")) {
                $this->showError(404, $response);
            }
        } catch (\Exception $e) {
            $log->error($e->getMessage(), ["PROGRAM" => "web/" . $router->getController() . "Controller"]);

            $this->showError(500, $response);
        }

        $this->response($response, $view);
    }

    /**
     * @param Router $router
     * @return View
     */
    protected function view(Router $router)
    {
        return Inject::method("Controllers\\" . ucfirst($router->getController()) . "Controller",
            $router->getAction(),
            array_merge($router->getRequest()->get, $router->getRequest()->post, [
                "router" => $router,
                "request" => $router->getRequest()
            ])
        );
    }

    /**
     * @param Response $response
     * @param View $view
     */
    protected function response(Response $response, View $view = null)
    {
        if ($view != null) {
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
     * @param $status
     * @param Response $response
     * @param null $message
     */
    protected function showError($status, Response &$response)
    {
        $response->setContent(file_get_contents(__DIR__ . '/Html/' . $status . '.html'));
        $response->setHeaders(['status' => $status]);
    }

    /**
     * @param Response $response
     * @param Router $router
     * @param $action
     * @return bool
     */
    protected function redirectError(Response &$response, Router $router, $action)
    {
        if (!method_exists("\\Controllers\\" . $router->getErrorController() . "Controller", $action)) {
            return false;
        }

        $response->setHeaders([
            'status' => 302,
            'Location' => $router->link($action, $router->getErrorController())
        ]);

        return true;
    }
}
