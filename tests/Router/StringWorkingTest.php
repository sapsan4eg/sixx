<?php

class StringWorkingTest extends PHPUnit_Framework_TestCase
{
    public function providerNames()
    {
        return [
            ['my world', 'hello{my world}is best'],
            ['hello', 'bad{hello}world'],
            ['controller', '{controller}'],
            ['action', '{action}'],
            ['controller', '{controller}rth'],
            ['action', 'rrr{action}'],
        ];
    }

    public function providerRoutes()
    {
        return [
            ['hard', 'hello{my world}is best', 'hellohardis best'],
            ['teacher', 'bad{hello}world', 'badteacherworld'],
            ['test', '{controller}', 'test'],
            ['index', '{action}', 'index'],
            ['home', '{controller}rth', 'homerth'],
            ['MyBalance', 'rrr{action}', 'rrrMyBalance'],
        ];
    }

    public function providerPatterns()
    {
        return [
            ['hello{my world}is best', 'hellohardis best'],
            ['bad{hello}world', 'badteacherworld'],
            ['{controller}', 'test'],
            ['{action}', 'index'],
            ['{controller}rth', 'homerth'],
            ['rrr{action}', 'rrrMyBalance'],
        ];
    }

    public function providerMap()
    {
        return [
            [['hello','world'], '/hello/world/'],
            [['hello','world'], 'hello/world/'],
            [['hello','world'], '/hello/world'],
            [['hello','world'], 'hello/world'],
            [['helloworld'], 'helloworld'],
        ];
    }

    public function providerArguments()
    {
        return [
            [['test' => 'my','str' => 1], 'test=my&str=1'],
            [['car' => 'megan'], 'car=megan'],
            [['trush' => 'cat'], '&&&hello=&&&trush=cat'],
            [[], '&&&&=&7&&7&=et4564wfgs&&&'],
            [[], ''],
        ];
    }

    public function providerUri()
    {
        return [
            ['?test=tester&cops', '/index.php?test=tester&cops'],
            ['http://six-x.org/?test=tester&cops', 'http://six-x.org/?test=tester&cops'],
            ['http://six-x.org?test=tester&cops', 'http://six-x.org/index.php?test=tester&cops'],
            ['http://six-x.org/Home/Index', 'http://six-x.org/Home/Index/'],
            ['http://six-x.org/Home/Index', 'http://six-x.org/Home/Index'],
        ];
    }

    public function provideItSameRoute()
    {
        return [
            ['{controller}/{action}/',],
            ['{action}/{year}', ['year' => '']],
            ['{test}/{year}/terter/tert/', ['year' => '', 'test' => '']],
        ];
    }

    /**
     * @dataProvider providerNames
     * @covers \Sixx\Router\StringWorking::getName
     */
    public function testGetName($find, $search)
    {
        $this->assertEquals($find, \Sixx\Router\StringWorking::getName($search));
    }

    /**
     * @dataProvider providerRoutes
     * @covers \Sixx\Router\StringWorking::clearRoute
     */
    public function testClearRoute($find, $pattern, $string)
    {
        $this->assertEquals($find, \Sixx\Router\StringWorking::clearRoute($pattern, $string));
    }

    /**
     * @dataProvider providerPatterns
     * @covers \Sixx\Router\StringWorking::samePatterns
     */
    public function testSamePatterns($pattern, $string)
    {
        $this->assertTrue(\Sixx\Router\StringWorking::samePatterns($pattern, $string));
    }

    /**
     * @dataProvider providerMap
     * @covers \Sixx\Router\StringWorking::map
     */
    public function testMap($expect, $string)
    {
        $this->assertEquals($expect, \Sixx\Router\StringWorking::map($string));
    }

    /**
     * @dataProvider providerArguments
     * @covers \Sixx\Router\StringWorking::arguments
     */
    public function testArguments($expect, $string)
    {
        $this->assertEquals($expect, \Sixx\Router\StringWorking::arguments($string));
    }

    /**
     * @dataProvider providerUri
     * @covers \Sixx\Router\StringWorking::clearUri
     */
    public function testClearUri($expect, $string)
    {
        $this->assertEquals($expect, \Sixx\Router\StringWorking::clearUri($string));
    }

    /**
     * @dataProvider provideitSameRoute
     * @covers \Sixx\Router\StringWorking::itSameRoute
     */
    public function testItSameRoute($route, $argements = [])
    {
        $this->assertTrue(\Sixx\Router\StringWorking::itSameRoute($route, $argements));
    }
}
