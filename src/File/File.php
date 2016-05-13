<?php

namespace Sixx\File;

/**
 * Sixx\File\File
 *
 * @package    Sixx
 * @subpackage File
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class File extends Arrays
{
    protected static $path = '/';
    protected $realArray = [];
    protected $safety = ['image/jpeg', 'image/png', 'application/pdf', 'text/plain'];
    protected $size = 2097151;
    protected $error = 0;

    /**
     * Constructor
     *
     * @param	string
     */
    public function __construct($path = null)
    {
        if (!empty($path)) {
            $this->path($path);
            $this->arrays();
        }
    }

    /**
     * sets the path
     *
     * @param   string
     * @return	object
     */
    public function path($paths = null)
    {
        if ($paths === null)
            return self::$path;

        self::$path = is_dir(realpath((string)$paths)) ? realpath((string)$paths) : '/';
        self::$path .= strrpos(self::$path, '/') === strlen(self::$path) -1 ? '' : '/';

        return $this;
    }

    /**
     * list files and directories inside the specified path
     *
     * @param   string
     * @return	bool
     */
    protected function arrays()
    {
        $this->realArray = $this->array = scandir(self::$path);
    }

    /**
     * assigns the local variable array of original
     *
     * @param   string
     * @return	object
     */
    public function original()
    {
        $this->array = $this->realArray;
        return $this;
    }

    /**
     * sets the path and creates an array of files
     *
     * @param   string
     * @return	object
     */
    public function files($path = null)
    {
        $this->path($path);
        $this->arrays();
        return $this;
    }

    /**
     * get info from files
     *
     * @param   string
     * @return	object
     */
    public function filesInfo()
    {
        $this->array = array_map(['\Sixx\File\File', 'info'], (array)$this->array);
        return $this;
    }

    /**
     * get info from file
     *
     * @param   string
     * @return	array
     */
    public static function info($string)
    {
        if (is_array($string))
            return array_map(['\Sixx\File\File', 'info'], $string);

        $string = self::$path . $string;

        if (file_exists($string)) {
            $info = new \SplFileInfo($string);
            return [
                'name' => $info->getBasename(),
                'type' => empty($info->getExtension()) ? filetype($string) : $info->getExtension(),
                'size' =>  $info->getSize(), 'last' => $info->getCTime()
            ];
        }

        return ['name' => basename($string), 'type' => 'not understand', 'size' => 'not understand', 'last' => 1];
    }

    /**
     * upload file
     *
     * @param   string
     * @return	mixed
     */
    public function upload($filename = '', $newname = '')
    {
        $this->error = $this->validate($filename);
        if ($this->error === 0) {
            $name = ( !empty($newname) ? $newname : str_replace('.', '', microtime (true)));
            $spl = new \SplFileInfo($name);
            $name = $spl->getBasename('.' . $spl->getExtension()) . '.' . (new \SplFileInfo($_FILES[$filename]['name']))->getExtension();

            if (move_uploaded_file($_FILES[$filename]['tmp_name'], self::$path . $name)) {
                return self::$path . $name;
            } else $this->error = 12;
        }

        return false;
    }

    /**
     * validate upload file
     *
     * @param   string
     * @return	int
     */
    protected function validate($filename = '')
    {
        if (!$this->uploaded($filename))
            return 9;

        if ($_FILES[$filename]['error'] > 0)
            return $_FILES[$filename]['error'];

        if ($_FILES[$filename]['size'] > $this->size || $_FILES[$filename]['size'] == 0)
            return 10;

        if (!count(array_keys($this->safety, $_FILES[$filename]['type'])) > 0)
            return 11;

        return 0;
    }

    /**
     * check is uploaded file
     *
     * @param   string
     * @return	bool
     */
    public function uploaded($filname = '')
    {
        return !empty($_FILES[$filname]);
    }

    /**
     * check is uploaded file
     *
     * @param   string
     * @return	bool
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * check exist file in directory
     *
     * @param   string
     * @return	bool
     */
    public function have($name = '')
    {
        return empty($name) ? false : file_exists(self::$path . (new \SplFileInfo($name))->getBasename());
    }

    /**
     * check is writable file
     *
     * @param   string
     * @return	bool
     */
    public function writable($name = '')
    {
        return empty($name) ? false : is_writable(self::$path . (new \SplFileInfo($name))->getBasename());
    }

    /**
     * delete file from directory
     *
     * @param   string
     * @return	bool
     */
    public function delete($name = '')
    {
        if ($this->have($name) && is_dir(self::$path . (new \SplFileInfo($name))->getBasename()))
            return $this->writable($name) ? rmdir(self::$path . (new \SplFileInfo($name))->getBasename()) : false;

        return $this->have($name) ?
            ($this->writable($name) ? unlink(self::$path . (new \SplFileInfo($name))->getBasename()) : false)
            : true;
    }

    /**
     * rename file in directory
     *
     * @param   string
     * @param   string
     * @return	bool
     */
    public function rename ($name = '', $newname = '')
    {
        if ($this->writable($name) && ( !$this->have($newname) ||  $this->writable($newname)))
            return rename(self::$path . (new \SplFileInfo($name))->getBasename(), self::$path . (new \SplFileInfo($newname))->getBasename());

        return false;
    }

    /**
     * copy file in directory
     *
     * @param   string
     * @param   string
     * @return	bool
     */
    public function copy($name = '', $newname = '')
    {
        if (!empty($name) && !empty($newname) && $this->writable($name) && (
                is_writable(realpath($newname)) ||
                !file_exists(realpath($newname)) &&
                is_writable(realpath(dirname($newname))))) {
            return copy(self::$path . (new \SplFileInfo($name))->getBasename(), realpath(dirname($newname)) . '/' . basename($newname));
        }
        return false;
    }

    /**
     * remove file in directory
     *
     * @param   string
     * @param   string
     * @return	bool
     */
    public function remove($name = '', $newname = '')
    {
        if (!empty($name) && !empty($newname) && $this->writable($name) && (
                is_writable(realpath($newname)) ||
                !file_exists(realpath($newname)) &&
                is_writable(realpath(dirname($newname))))) {
            return rename(self::$path . (new \SplFileInfo($name))->getBasename(), realpath(dirname($newname)) . '/' . basename($newname));
        }
        return false;
    }

    /**
     * make new directory
     *
     * @param   string
     * @return	bool
     */
    public function newdir($name = '')
    {
        if (!empty($name) && !file_exists(self::$path . explode('.', basename($name))[0]))
            return mkdir(self::$path . explode('.', basename($name))[0]);

        return false;
    }
}
