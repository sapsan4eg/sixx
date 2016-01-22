<?php

namespace Sixx\File;

/**
 * Sixx\File\Image
 *
 * @package    Sixx
 * @subpackage File
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class Image
{
    protected $image;
    protected $types = ['jpg', 'gif', 'png', 'jpeg'];

    /**
     * Constructor
     *
     * @param	string
     */
    public function __construct($name = null)
    {
        ! empty($name) ? $this->take($name) : null;
    }

    /**
     * check is image file
     *
     * @param   string
     * @return	bool
     */
    protected function have($name)
    {
        if(! empty($name) && file_exists(realpath($name)) &&
            count(array_keys($this->types, strtolower((new \SplFileInfo($name))->getExtension()))) > 0) {
            return true;
        } else trigger_error('Image class exepted real image');

        return false;
    }

    /**
     * take image from file
     *
     * @param   string
     * @return	bool
     */
    public function take($name)
    {
        if($this->have($name)) {
            $type = (new \SplFileInfo($name))->getExtension();
            $keys = array_keys($this->types, $type);
            if(count($keys) > 0) {
                $func = 'imagecreatefrom' . str_replace('jpg', 'jpeg', $this->types[$keys[0]]);

                if(function_exists($func)) {
                    $this->image = [
                        'path'  => realpath($name),
                        'info'  => getimagesize($name),
                        'type'  => (new \SplFileInfo($name))->getExtension(),
                        'image' => $func(realpath($name))
                    ];
                } else trigger_error('Function ' . $func . ' not exist');
            } else trigger_error('Not supported image format');
        }

        return $this;
    }

    /**
     * save image
     *
     * @param   string
     * @return	bool
     */
    public function save($name)
    {
        if(! empty($name) && ! empty($this->image) && is_resource($this->image['image'])) {
            $real = realpath(dirname($name)) . '/' . explode('.', basename($name))[0] . '.' . $this->image['type'];

            if( is_writable($real) || ! file_exists($real) &&
                is_writable(realpath(dirname($name)))) {
                $func = 'image' . str_replace('jpg', 'jpeg', $this->image['type']);
                $func($this->image['image'], $real);
                imagedestroy($this->image['image']);
                $this->image = [];
                return true;
            }
        }
        return false;
    }
}
