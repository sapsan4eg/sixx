<?php

class StorageTest extends PHPUnit_Framework_TestCase
{
    public function providerSet()
    {
        return [
            [
                'hello',
                'test',
                'hello',
            ]
        ];
    }

    /**
     * @dataProvider providerSet
     * @param $need
     * @param $val
     * @param $new
     */
    public function testSet($need, $val, $new)
    {
        $storage = new \Sixx\Engine\Storage();
        $storage->join = $val;
        $storage->join = $new;
        $this->assertEquals($need, $storage->join);
    }

    /**
     * @dataProvider providerSet
     * @param $need
     * @param $val
     * @param $new
     */
    public function testSetArray($need, $val, $new)
    {
        $storage = new \Sixx\Engine\Storage();
        $storage->join = ['test' => $val];
        $storage->join['test'] = $new;
        $this->assertEquals($need, $storage->join['test']);
    }
}
