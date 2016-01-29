<?php

namespace Sixx;

/**
 * Sixx\RESTController
 *
 * @package    Sixx
 * @subpackage
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class RESTController extends Controller
{
    protected $variables       = [];
    protected $userId          = 0;

    /**
     * Constructor
     */
    protected function afterConstruct()
    {
        $this->view = new Engine\View([
            'ControllerName' => $this->router->route['controller'],
            'ActionName'     => $this->router->route['action'],
            'RequestedUrl'   => $this->request->url,
            'router'         => $this->router,
            'response'       => $this->response,
        ]);

        if (! $this->legitimated($this->permissions($this->router->route['controller'], $this->router->route['action']))) {
            $this->response->response();
            exit;
        }
    }

    /**
     * @param string $controller
     * @param string $action
     * @return array
     */
    protected function permissions($controller, $action)
    {
        $doc = new \ReflectionClass($controller . 'Controller');
        $action = $this->getParams($doc->getMethod($action)->getDocComment());
        $controller = $this->getParams($doc->getDocComment());
        return array_merge($controller, $action);
    }

    /**
     * @param string $string
     * @return array
     */
    protected function getParams($string = '')
    {
        $string = str_replace(['/**', '*/'], '', str_replace("\t", ' ', $string));
        $params = explode('*', $string);
        $array = [];

        foreach ($params as $param) {
            $param = trim($param);

            while (strpos($param, '  ') !== false) {
                $param = str_replace('  ', ' ', $param);
            }

            if (! empty($param) && strpos($param, '@') !== false) {
                $key = substr($param, 0, strpos($param, ' '));
                $value = substr($param, strpos($param, ' ') + 1);

                if (in_array($key, ['@methods', '@autorization', '@autorizationType', '@variables'])
                    && ! empty(trim($value))
                )
                    $array[substr($key, 1)] = trim($value);
            }
        }

        return $array;
    }

    /**
     * check is legitimate request
     * @param array $array
     * @return  bool
     */
    protected function legitimated($array = [])
    {
        #if (! empty($array['autorization']) && empty($array['autorizationType']))
            $array['autorizationType'] = 'basic';

        if (! empty($this->request->headers['PHP_AUTH_USER']) && ! empty($this->request->headers['PHP_AUTH_PW'])
            && $this->haveUser($this->request->headers['PHP_AUTH_USER'], $this->request->headers['PHP_AUTH_PW'])
        ) {
            return $this->allowedMethod();
        }

        if (empty($this->response->getContent()))
            $this->response->setContent($this->view->JsonResult([
                'code'    => 401,
                'error'   => 'You don\'t send authorization values.',
                'message' => 'You don\'t send authorization values.',
            ]));

        $this->response->setHeaders(['status' => 401]);

        return false;
    }

    /**
     * Check we have that user
     *
     * @param   string  $username
     * @param   string  $password
     * @return  bool
     */
    protected function haveUser($username, $password)
    {
        if ($this->defaultUsername != $username) {
            if (($user = $this->redis->autorization->getUserByNamePwd(strtolower(\Sixx\Protection\Protector::xssClean($username)), $password))) {
                $this->userId = (int)$user['id'];
                return true;
            }
        } elseif ($this->defaultPwd == $password) {
            return true;
        }

        $this->response->setContent($this->view->JsonResult([
            'code'    => 401,
            'error'   => 'Username or password not found',
            'message' => 'Username = ' . $username . ' AND password = ' . $password . ' not found.',
        ]));

        return false;
    }

    /**
     * Check this method allowed for that action
     *
     * @return  bool
     */
    protected function allowedMethod()
    {
        if (empty($this->actions[$this->action]['methods']))
            return $this->variables();

        if (false !== array_search($this->request->method, $this->actions[$this->action]['methods'])) {
            if ((empty($this->request->headers['Accept']) || $this->request->headers['Accept'] != 'application/json')
                && ($this->request->method == 'POST' || $this->request->method == 'PUT'
                || $this->request->method == 'PATCH' || $this->request->method == 'DELETE')
            ) {
                $this->response->setContent($this->view->JsonResult([
                    'code' => 415,
                    'error' => 'Unsupported media type.',
                    'message' => ' Accept: application/json HTTP header wasn\'t send.',
                ]));
                $this->response->setHeaders(['status' => 415]);
                return false;
            } elseif((empty($this->request->headers['CONTENT_TYPE']) ||  $this->request->headers['CONTENT_TYPE'] != 'application/json')
                && ($this->request->method == 'POST' || $this->request->method == 'PUT'
                    || $this->request->method == 'PATCH' || $this->request->method == 'DELETE')
            ) {
                $this->response->setContent($this->view->JsonResult([
                    'code' => 415,
                    'error' => 'Unsupported media type.',
                    'message' => ' Content-Type: application/json HTTP header wasn\'t send.',
                ]));
                $this->response->setHeaders(['status' => 415]);
                return false;
            }

            return $this->variables();
        }

        $this->response->setContent($this->view->JsonResult([
            'code' => 405,
            'error' => 'Method Not Allowed.',
            'message' => 'Method ' . $this->request->method . ' Not Allowed by this action.',
        ]));
        $this->response->setHeaders(['status' => 405]);
        return false;
    }

    /**
     * Check all variables send to action
     *
     * @return  bool
     */
    protected function variables()
    {
        if (empty($this->actions[$this->action]['variables']))
            return true;

        $return = true;

        if ($this->request->method == 'GET')
            $this->variables = $this->request->get;
        else {
            $string = file_get_contents('php://input');
            try {
                $this->variables = json_decode($string, true);
            } catch(\Exception $e) {
                $this->response->setContent($this->view->JsonResult([
                    'code' => 400,
                    'error' => 'Bad Request',
                    'message' => 'Bad Request - not valid json parameters. Your param ' . $string,
                ]));
                $this->response->setHeaders(['status' => 400]);
                return false;
            }
        }

        foreach ($this->actions[$this->action]['variables'] as $variable) {
            if (empty($this->variables[$variable]))
                $return = false;
        }

        if ($return == false) {
            $this->response->setContent($this->view->JsonResult([
                'code' => 400,
                'error' => 'Bad Request - Often missing a required parameter.',
                'message' => 'Bad Request - Often missing a required parameter. Your param ' . json_encode($this->variables),
            ]));

            $this->response->setHeaders(['status' => 400]);
        }

        return $return;
    }
}
