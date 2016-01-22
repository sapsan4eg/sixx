<?php

namespace Sixx\Engine;

/**
 * Sixx\Engine\Controller
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
class Controller extends Object
{
    protected function afterConstruct()
    {
        $data = [
            'ControllerName' => $this->router->route['controller'],
            'ActionName' => $this->router->route['action'],
            'RequestedUrl'=> $this->request->url,
            'router' => $this->router,
            'response' => $this->response,
            'config' => $this->config,
        ];

        if(isset($this->request->session->data['message'])) {
            $data['message'] = $this->request->session->data['message'];
            unset($this->request->session->data['message']);
        }

        $this->view = new View($data);
    }
}
