<?php

namespace Sixx\Exceptions;

use Sixx\Config\ConfigInterface;
use Sixx\Log\LoggerInterface;

class ExceptionCatcher
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var ExceptionController
     */
    protected $controller;

    /**
     * ExceptionCatcher constructor.
     * @param ConfigInterface $config
     * @param LoggerInterface $log
     * @param ExceptionController $controller
     */
    public function __construct(ConfigInterface $config, LoggerInterface $log, ExceptionController $controller)
    {
        $this->config = $config;
        $this->log = $log;
        $this->controller = $controller;
    }

    /**
     * @param \Exception $e
     * @return \Sixx\View
     */
    public function showException(\Exception $e)
    {
        if (!empty($this->config->exceptions)) {
            foreach ($this->config->exceptions as $exception) {
                if ($exception['class'] == get_class($e) && !empty($exception['code']) && !empty($exception['message'])) {

                    if (!empty($exception['logged']) && method_exists($this->log, $exception['logged'])) {
                        $method = $exception['logged'];
                        $this->log->$method($e->getMessage(), ['PROGRAM' => 'web/']);
                    }

                    if (!empty($e->getMessage())) {
                        $exception['message'] = $e->getMessage();
                    }

                    if (!empty($exception['type']) && $exception['type'] == 'json') {
                        return $this->controller->JsonError($exception['code'], $exception['message']);
                    } elseif (!empty($exception['type']) && $exception['type'] == 'redirect' && !empty($exception['controller']) && class_exists($exception['controller'])) {
                        $action = (empty($exception['action'] || !method_exists($exception['controller'], $exception['action'])) ? 'Index' : $exception['action']);
                        $arguments = (empty($exception['arguments']) || !is_array($exception['arguments'])) ? [] : $exception['arguments'];
                        return $this->controller->RedirectError($exception['controller'], $action, $arguments);
                    } else {
                        return $this->controller->Error($exception['code'], $exception['message']);
                    }
                }
            }
        }

        return $this->controller->Error(500, 'Something going wrong');
    }
}
