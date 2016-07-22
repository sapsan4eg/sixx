<?php

namespace Sixx;

use Sixx\DependencyInjection\Inject;

class Application
{
    protected $object;

    /**
     * Application constructor.
     * @param string $class
     * @param null|string $pathToConfig
     */
    public function __construct($class, $pathToConfig = null)
    {
        Inject::bind('Sixx\\Config\\ConfigInterface', ['default' => ['name' => 'Sixx\\Config\\Config', 'single' => true, 'parameters' => ['path' => $pathToConfig]]]);
        require_once "startup.php";
        Inject::instantiation('Sixx\\Config\\ConfigInterface');
        Inject::instantiation('Sixx\Log\LoggerInterface');
        $this->object = Inject::instantiation($class);
    }

    /**
     * @return object
     */
    public function getApplication()
    {
        return$this->object;
    }
}
