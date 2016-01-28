<?php

namespace Sixx\Engine;

/**
 * Sixx\Engine\View
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
final class View
{
    private $privateData = [];
    public $router;

    /**
     * Constructor
     *
     * @param	array
     */
    public function __construct($data = [])
    {
        $this->set($data);
    }

    /**
     * @access public
     * @return array
     */
    public function get()
    {
        return $this->privateData;
    }

    /**
     * @access	public
     * @param	array
     */
    public function set($data)
    {
        if(isset($data['router'])) {
            $this->router = $data['router'];
            unset($data['router']);
        }
        $this->privateData = $data;
    }

    /**
     * @access public
     * @param string
     * @param mixed
     */
    public function __set($key, $value)
    {
        $this->privateData[$key] = $value;
    }

    /**
     * @access public
     * @param string
     * @return mixed
     */
    public function __get($key)
    {
        return ($this->has($key) ? $this->privateData[$key] : null);
    }

    /**
     * check has key in array
     *
     * @access	public
     * @param	string
     * @return	mixed
     */
    public function has($key)
    {
        return isset($this->privateData[$key]);
    }

    /**
     * @access public
     * @param string
     * @return mixed
     */
    public function __isset($key)
    {
        return isset($this->privateData[$key]);
    }

    /**
     * @access public
     * @param string $actionName
     * @param string
     * @param string|bool $layout
     * @throws \Sixx\Exceptions\NotfoundException
     * @return string
     */
    public function ViewResult($actionName = '', $controllerName = '', $layout = true)
    {
        if (empty($actionName))
            $actionName = $this->privateData['ActionName'];

        if (empty($controllerName))
            $controllerName = $this->privateData['ControllerName'];

        $expansion = ! empty($this->config->file_view) ? $this->config->file_view : 'tpl';

        $dir = \Sixx\Load\Loader::slash($this->config->dir_base);

        $dir .= \Sixx\Load\Loader::slash(! empty($this->config->dir_views) ? $this->config->dir_views : 'views');

        $file = strtolower($dir . $controllerName . '/' . $actionName . '.' . $expansion);

        if ($layout != false) {

            if(is_string($layout) && strlen($layout) > 0)
                $layoutFile = $layout;
            else
                $layoutFile = ! empty($this->config->file_layout) ? $this->config->file_layout : 'layout';

            $dir .= \Sixx\Load\Loader::slash(! empty($this->config->dir_shared) ? $this->config->dir_shared : 'shared');

            $file = strtolower($dir . $layoutFile . '.' . $expansion);
            $this->renderBody = $this->ViewResult($actionName, $controllerName, false);
        }

        if (file_exists($file)) {
            extract($this->privateData);
            ob_start();
            include($file);
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        } else {
            throw new \Sixx\Exceptions\NotfoundException('Error: Could not find view ' . $file . '!');
        }
    }

    /**
     * @access public
     * @param string
     * @param string
     * @return string
     */
    public function PartialViewResult($actionName = '', $controllerName = '')
    {
        return $this->ViewResult($actionName, $controllerName, false);
    }

    /**
     * redirect browser to new page
     *
     * @access public
     * @param string
     * @param string
     * @param array
     */
    public function RedirectToAction($action = '', $controller = '', $arguments = [])
    {
        $this->response->setHeaders(['status' => 302, 'Location' => $this->router->link($action, $controller, $arguments)]);
    }

    /**
     * generates error page with status 404
     *
     * @access public
     * @param string $actionName
     * @param string $controllerName
     * @param string|bool $layout
     * @return string
     */
    public function NotFoundResult($actionName = '', $controllerName = '',  $layout = true)
    {
        $this->response->setHeaders(['status' => 404]);
        return $this->ViewResult($actionName, $controllerName, $layout);
    }

    /**
     * @access public
     * @param string $file
     * @param string $filename
     * @param mixed $type
     * @throws \Exception
     * @param string
     */
    public function FileResult($file = '', $filename = '', $type = null)
    {
        if (empty($type)) {
            if ( file_exists($file)) {
                if (strlen($filename) == 0)
                    $filename = basename($file);
                else
                    $filename .= '.' . substr(strrchr($file, '.'), 1);

                $this->fileHeaders($filename, filesize($file));
                ob_clean();
                flush();
                readfile($file);
                exit();
            } else {
                throw new \Exception('Error: Could not find file ' . $file . '!');
            }
        } elseif (is_string($file)) {
            $this->fileHeaders($filename . "." . $type, strlen($file));
            ob_clean();
            flush();
            ob_start();
            echo $file;
            ob_end_flush();
            exit();
        } else {
            throw new \Exception('Error: you give to output buffer something wrong !');
        }
    }

    /**
     * return file stream to browser
     *
     * @access	public
     * @param	string
     * @param	string
     */
    protected function fileHeaders($name, $size)
    {
        $this->response->setHeaders(
            [
                'Content-Description' => 'File Transfer',
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename=' . $name,
                'Content-Transfer-Encoding' => 'binary',
                'Expires' => '0',
                'Cache-Control' => 'must-revalidate',
                'Pragma' => 'public',
                'Content-Length' => $size,
            ]
        );
    }

    /**
     * return json string format
     *
     * @access public
     * @param array|string $array
     * @return string
     */
    public function JsonResult($array = [])
    {
        $this->response->setHeaders(['Content-Type' => 'application/json']);

        if (is_string($array))
            return $array;

        return json_encode($array);
    }
}
