<?php

namespace Sixx;

use Sixx\Exceptions\NotFoundFileException;

/**
 * Sixx\View
 *
 * @package    Sixx
 * @subpackage
 * @category   Core
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
final class View
{
    protected $privateData = [];

    protected $content;
    protected $headers;
    protected $actionName;
    protected $controllerName;

    /**
     * @access public
     * @return array
     */
    public function get()
    {
        return $this->privateData;
    }

    /**
     * @param array
     */
    public function set(array $data)
    {
        $this->privateData = $data;
    }

    /**
     * @param string
     * @param mixed
     */
    public function __set($key, $value)
    {
        $this->privateData[$key] = $value;
    }

    /**
     * @param string
     * @return mixed
     */
    public function __get($key)
    {
        return ($this->has($key) ? $this->privateData[$key] : null);
    }

    /**
     * @param string
     * @return mixed
     */
    public function has($key)
    {
        return isset($this->privateData[$key]);
    }

    /**
     * @param string
     * @return mixed
     */
    public function __isset($key)
    {
        return isset($this->privateData[$key]);
    }

    /**
     * @param string $actionName
     * @param string
     * @param string|bool $layout
     * @throws \Sixx\Exceptions\NotFoundFileException
     * @return $this
     */
    public function ViewResult($actionName = '', $controllerName = '', $layout = true)
    {
        if (empty($actionName))
            $actionName = $this->actionName;

        if (empty($controllerName))
            $controllerName = $this->controllerName;

        $expansion = !empty($this->config->file_view) ? $this->config->file_view : 'tpl';

        $dir = slash(DIR_BASE);

        $dir .= slash(!empty($this->config->dir_views) ? $this->config->dir_views : 'views');

        $file = strtolower($dir . $controllerName . '/' . $actionName . '.' . $expansion);

        if ($layout != false) {

            if (is_string($layout) && strlen($layout) > 0)
                $layoutFile = $layout;
            else
                $layoutFile = !empty($this->config->file_layout) ? $this->config->file_layout : 'layout';

            $dir .= slash(!empty($this->config->dir_shared) ? $this->config->dir_shared : 'shared');

            $file = strtolower($dir . $layoutFile . '.' . $expansion);
            $this->ViewResult($actionName, $controllerName, false);
            $this->renderBody = $this->getContent();
        }

        if (file_exists($file)) {
            extract($this->privateData);
            ob_start();
            include($file);
            $this->content = ob_get_contents();
            ob_end_clean();
            return $this;
        } else {
            throw new NotFoundFileException('Error: Could not find view ' . $file . '!');
        }
    }

    /**
     * @param string
     * @param string
     * @return $this
     */
    public function PartialViewResult($actionName = '', $controllerName = '')
    {
        return $this->ViewResult($actionName, $controllerName, false);
    }

    /**
     * Redirect browser to new page
     *
     * @param string $action
     * @param string $controller
     * @param array|null $arguments
     * @return $this
     */
    public function RedirectToAction($action = '', $controller = '', array $arguments = null)
    {
        $this->response->setHeaders(['status' => 302, 'Location' => $this->router->link($action, $controller, $arguments)]);
        return $this;
    }

    /**
     * Generates error page with status 404
     *
     * @access public
     * @param string $actionName
     * @param string $controllerName
     * @param string|bool $layout
     * @return $this
     */
    public function NotFoundResult($actionName = '', $controllerName = '',  $layout = true)
    {
        $this->headers = ['status' => 404];
        return $this->ViewResult($actionName, $controllerName, $layout);
    }

    /**
     * @access public
     * @param string $file
     * @param string $filename
     * @param mixed $type
     * @throws NotFoundFileException
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
                $this->content = file_get_contents($file);
            } else {
                throw new NotFoundFileException('Error: Could not find file ' . $file . '!');
            }
        } elseif (is_string($file)) {
            $this->fileHeaders($filename . "." . $type, strlen($file));
            ob_clean();
            flush();
            ob_start();
            $this->content = $file;
            ob_end_flush();
            exit();
        } else {
            throw new NotFoundFileException('Error: you give to output buffer something wrong !');
        }
    }

    /**
     * Return file stream to browser
     *
     * @access public
     * @param string
     * @param string
     */
    protected function fileHeaders($name, $size)
    {
        $this->headers =
            [
                'Content-Description' => 'File Transfer',
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename=' . $name,
                'Content-Transfer-Encoding' => 'binary',
                'Expires' => '0',
                'Cache-Control' => 'must-revalidate',
                'Pragma' => 'public',
                'Content-Length' => $size,
            ];
    }

    /**
     * Return json string format
     *
     * @access public
     * @param array|string|null $array
     * @return $this
     */
    public function JsonResult($array = null)
    {
        $this->headers = ['Content-Type' => 'application/json'];

        if (is_string($array))
            return $array;

        $this->content = json_encode($array);

        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setController($controller)
    {
        $this->controllerName = $controller;
    }

    public function setAction($action)
    {
        $this->actionName = $action;
    }
}
