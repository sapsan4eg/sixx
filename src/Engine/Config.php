<?php

namespace Sixx\Engine;
use Sixx\Exceptions\NotfoundException;

/**
 * Sixx\Engine\Config
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
class Config
{
    public function __construct($file = '')
    {
        if (! defined('DIR_BASE'))
            throw new NotfoundException('Dir base not declarated, use define DIR_BASE to declarate');

        if (file_exists($file)) {

            $content = file_get_contents($file);
            $content = explode(PHP_EOL, $content);
            $array = ['dir_views', 'dir_errors', 'dir_shared', 'dir_email_views'];

            $dirs = [];

            foreach ($content as $value) {
                if (strpos($value, '=') > 0) {
                    $name = strtolower(trim(substr($value, 0 , strpos($value, '='))));
                    $val = trim(substr($value, strpos($value, '=') + 1));

                    if (strpos($name, 'dir_') === 0 && ! in_array($name, $array)) {
                         $dirs[] = \Sixx\Load\Loader::slash(\Sixx\Load\Loader::slash(DIR_BASE) . $val);
                    } else
                        $this->$name = $val;
                }
            }

            #if(count($dirs) > 0)
                $this->dirs = $dirs;

            if (empty($this->dir_view))
                $this->dir_view = \Sixx\Load\Loader::slash(DIR_BASE) . 'views';

            if (empty($this->dir_shared))
                $this->dir_shared = 'shared';

        } else {
            throw new NotfoundException('Cannot find config file: ' . $file);
        }
    }

    public function __get($name)
    {
        if (! isset($this->$name))
            return null;
        else
            return $this->$name;
    }
}
