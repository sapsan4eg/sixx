<?php

namespace Sixx\Config;

use Sixx\DependencyInjection\Inject;

/**
 * Class Config
 * @package Sixx\Config
 * @property array $exceptions
 * @property array $dependencyinjection
 */
class Config implements ConfigInterface
{
    protected $config = [];

    /**
     * Config constructor.
     * @param null $path
     */
    public function __construct($path = null)
    {
        $this->getConfigs(__DIR__ . '/Default/');

        if (!empty($path) && is_string($path) && is_dir($path)) {
            $this->getConfigs($path);
        }

        if (!empty($this->config['dependencyinjection'])) {
            Inject::bindByArray($this->config['dependencyinjection']);
        }
    }

    /**
     * @param array $config
     * @param string $name
     * @param array $value
     */
    protected function setConfig(array &$config, $name, array $value)
    {
        $name = strtolower($name);

        if (empty($config[$name])) {
            $config[$name] = $value;
        } else {
            $config[$name] = array_merge($config[$name], $value);
        }
    }

    /**
     * Check directory and get json configs.
     * @param $dir
     */
    protected function getConfigs($dir)
    {
        if (is_dir($dir) && is_readable($dir)) {
            foreach (new \DirectoryIterator($dir) as $dirInfo) {
                if ($dirInfo->isDot()) {
                    continue;
                }

                $config = [];

                if ($dirInfo->isDir()) {
                    foreach (new \DirectoryIterator(slash($dir) . $dirInfo->getFilename()) as $fileInfo) {
                        if ($fileInfo->isDot() || $fileInfo->isDir() || !strpos($fileInfo->getFilename(), '.json')) {
                            continue;
                        }
                        $data = json_decode($fileInfo->openFile(), true);
                        if (is_array($data)) {
                            $config = array_merge($config, $data);
                        }
                    }
                }

                if (!is_array($config)) {
                    continue;
                }

                $this->setConfig($this->config, $dirInfo->getFilename(), $config);
            }
        }
    }

    /**
     * Get config
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        return null;
    }

    /**
     * Check has config
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * Set config
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }
}
