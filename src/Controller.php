<?php

namespace Sixx;

/**
 * Sixx\Controller
 *
 * @package    Sixx
 * @subpackage
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 *
 * @property \Sixx\Engine\View $view
 * @property \Sixx\Engine\Model $model
 * @property \Sixx\Entity $entity
 */
class Controller extends Engine\Object
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

        if (isset($this->request->session->data['message'])) {
            $data['message'] = $this->request->session->data['message'];
            unset($this->request->session->data['message']);
        }

        $this->view = new Engine\View($data);
    }
}
