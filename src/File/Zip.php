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
class Zip {

    /**
     * Zip data in string
     *
     * @var string
     */
    public $data = '';

    /**
     * Zip data for a directory in string
     *
     * @var string
     */
    public $dir = '';

    /**
     * Number of files/folder in zip file
     *
     * @var int
     */
    public $count = 0;

    /**
     * relative offset of local header
     *
     * @var int
     */
    public $lenData = 0;

    /**
     * The level of compression
     *
     * Ranges from 0 to 9, with 9 being the highest level.
     *
     * @var	int
     */
    public $level = 2;

    /**
     * The main path
     *
     * @var	int
     */
    public $path = '';

    /**
     * Add Data to Zip
     *
     * Lets you add files to the archive. If the path is included
     * in the filename it will be placed within a directory. Make
     * sure you use add_dir() first to create the folder.
     *
     * @param	mixed	$file    	A single realfile
     * @param	string	$path		Single filepath
     * @return	object
     */
    public function addData($file, $path = "", $byteArray = false)
    {
        $name = "";
        $data = "";
        $mTime = [];

        if($byteArray == false) {
            if(is_array($file)) {
                foreach($file as $f) {
                    $this->addData($f, $path);
                }
            } else {
                if (file_exists($file)) {
                    if(is_dir($file) === FALSE && FALSE !== ($data = file_get_contents($file))) {
                        $mTime = getdate(filemtime($file));
                        $name = $this->name($file, $path);
                    } else return $this;
                } else return $this;
            }

        } elseif (is_array($file) && ! empty($file['data']) && ! empty($file['name'])) {
            $name = $this->name($file['name'], $path);
            $mTime = getdate(time());
            $data = $file['data'];
        } else return $this;

        $mTime = ['file_mtime' => ($mTime['hours'] << 11) + ($mTime['minutes'] << 5) + $mTime['seconds'] / 2,
            'file_mdate' => (($mTime['year'] - 1980) << 9) + ($mTime['mon'] << 5) + $mTime['mday']];

        $this->toZip($data, $name, $mTime);

        return $this;
    }

    /**
     * Add Data to Zip
     *
     * @param string $data string bytes
     * @param string $name the data to be encoded
     * @param array $mTime
     * @return void
     */
    protected function toZip($data, $name, $mTime)
    {
        $uncompressed_size = strlen($data);
        $crc32  = crc32($data);
        $gzdata = substr(gzcompress($data, $this->level), 2, -4);
        $compressed_size = strlen($gzdata);
        $this->data .=
            "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00"
            .pack('v', $mTime['file_mtime'])
            .pack('v', $mTime['file_mdate'])
            .pack('V', $crc32)
            .pack('V', $compressed_size)
            .pack('V', $uncompressed_size)
            .pack('v', strlen($name)) // length of filename
            .pack('v', 0) // extra field length
            .$name
            .$gzdata // "file data" segment
            .pack('V', $crc32)
            .pack('V', $compressed_size)
            .pack('V', $uncompressed_size);

        $this->dir .=
            "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00"
            .pack('v', $mTime['file_mtime'])
            .pack('v', $mTime['file_mdate'])
            .pack('V', $crc32)
            .pack('V', $compressed_size)
            .pack('V', $uncompressed_size)
            .pack('v', strlen($name)) // length of filename
            .pack('v', 0) // extra field length
            .pack('v', 0) // file comment length
            .pack('v', 0) // disk number start
            .pack('v', 0) // internal file attributes
            .pack('V', 32) // external file attributes - 'archive' bit set
            .pack('V', $this->lenData) // relative offset of local header
            .$name;

        $this->lenData = strlen($this->data);
        $this->count++;
    }

    /**
     * Get the Zip file
     *
     * @return	string	(binary encoded)
     */
    protected function name($name, $path)
    {
        $name = str_replace('\\', '/', $name);

        if (strrpos($name, '/') !== false) {
            $name = substr($name, strrpos($name, '/') + 1);
        }

        if (strlen($path) > 0) {
            $name = (strrpos($path, '/') == (strlen($path) - 1) ? ($path . "") : ($path . "/")) . $name;
        } elseif (strlen($this->path) > 0) {
            $name = (strrpos($this->path, '/') == (strlen($this->path) - 1) ? ($this->path . "") : ($this->path . "/")) . $name;
        }

        return $name;
    }

    /**
     * Get the Zip file
     *
     * @return	string	(binary encoded)
     */
    public function getZip()
    {
        if ($this->count === 0) {
            return false;
        }

        return $this->data
        .$this->dir . "\x50\x4b\x05\x06\x00\x00\x00\x00"
        .pack('v', $this->count) // total # of entries "on this disk"
        .pack('v', $this->count) // total # of entries overall
        .pack('V', strlen($this->dir)) // size of central dir
        .pack('V', strlen($this->data)) // offset to start of central dir
        ."\x00\x00"; // .zip file comment length
    }
}
