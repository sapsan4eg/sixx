<?php

namespace Sixx\File;

/**
 * Sixx\File\Zip
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
class Arrays
{
    protected $array = [];
    protected static $from;
    protected static $to;
    protected static $search;

    /**
     * return array
     *
     * @return	array
     */
    public function toArray()
    {
        return (array)$this->array;
    }

    /**
     * extract a slice of the array
     *
     * @param   int
     * @param   int
     * @return	object
     */
    public function limit($from, $count = null)
    {
        $count = empty($count) ? $from >= 0 ? (int)$from : 0 : (int)$count;
        $from = $count == $from ? 0 : (int)$from;
        $this->array = array_slice((array)$this->array, $from, $count);

        return $this;
    }

    /**
     *  convert string to requested character encoding
     *
     * @param   string
     * @param   string
     * @return	object
     */
    public function iconv($from, $to)
    {
        self::$from = $from;
        self::$to = $to;
        $this->array = array_map(['\Sixx\File\Arrays', 'iconvBack'], (array)$this->array);

        return $this;
    }

    /**
     * callback function to convert string to requested character encoding
     *
     * @param   string
     * @return	string
     */
    protected static function iconvBack($string)
    {
        if (is_array($string)) {
            return array_map(['\Sixx\File\Arrays', 'iconvBack'], $string);
        }

        return iconv(self::$from, self::$to, $string);
    }

    /**
     *  check contains string into array
     *
     * @param   string
     * @return	object
     */
    public function contains($string)
    {
        Arrays::$search = $string;
        $this->array = array_filter((array)$this->array, ['\Sixx\File\Arrays', 'contain']);

        return $this;
    }

    /**
     * callback function to check contains string into array
     *
     * @param   string
     * @return	bool
     */
    protected static function contain($string)
    {
        if (is_array($string)) {
            return array_filter($string, ['\Sixx\File\Arrays', 'contain']);
        }

        return strpos($string, Arrays::$search) > -1;
    }

    /**
     *  Split an array into pages
     *
     * @return	array
     */
    public function pages($count = null)
    {
        $count = empty($count) ? 10 : (int)$count;
        $this->array = array_chunk((array)$this->array, $count);

        return $this;
    }
}
