<?php

class ExceptionCatcherTest extends PHPUnit_Framework_TestCase
{
    public function  testShowException()
    {
        $config = $this->getMockBuilder('Sixx\\Config\\ConfigInterface')->disableOriginalConstructor()->getMock();
        $log = $this->getMockBuilder('Sixx\\Log\\LoggerInterface')->disableOriginalConstructor()->getMock();
        $controller = $this->getMockBuilder('Sixx\\Exceptions\\ExceptionController')->disableOriginalConstructor()->getMock();
        $router = $this->getMockBuilder('Sixx\\Router')->disableOriginalConstructor()->getMock();
        $catcher = new \Sixx\Exceptions\ExceptionCatcher($config, $log, $controller);

    }
}
