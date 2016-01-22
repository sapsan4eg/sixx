<?php

class ValidationTest extends PHPUnit_Framework_TestCase
{
    public function providerValidTrue()
    {
        return [
            [
                'qwerty',
                'required',
            ],
            [
                10,
                'required|numeric|isNumeric|integer|isNatural|isNaturalNoZero|matches[10]|minLength[2]|maxLength[2]|exactLength[2]',
            ],
            [
                'sapsan4eg@ya.ru',
                'validEmail',
            ],
            [
                'test ',
                'trim|matches[test]',
            ],
            [
                'test',
                'alpha',
            ],
            [
                11.2,
                'decimal|greaterThan[11.1999]|lessThan[11.2000001]',
            ],
        ];
    }

    public function providerValidFalse()
    {
        return [
            [
                '',
                'required',
            ],
            [
                '10s',
                'numeric|matches[10]|minLength[2]|maxLength[2]|exactLength[2]',
            ],
            [
                'sapsan4eg@ya@.ru',
                'validEmail',
            ],
            [
                'test ',
                'matches[test]',
            ],
            [
                'test1',
                'alpha',
            ],
        ];
    }

    /**
     * @dataProvider providerValidTrue
     * @param $string
     * @param $rules
     */
    public function testValidTrue($string, $rules)
    {
        $object = new \Sixx\Protection\Validation();

        $this->assertTrue($object->valid($string, $rules));
    }

    /**
     * @dataProvider providerValidFalse
     * @param $string
     * @param $rules
     */
    public function testValidFalse($string, $rules)
    {
        $object = new \Sixx\Protection\Validation();

        $this->assertFalse($object->valid($string, $rules));
    }
}