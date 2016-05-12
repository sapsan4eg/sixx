<?php

class ForwardRouteTest extends PHPUnit_Framework_TestCase
{
    public function providerUri()
    {
        return [
            [
                [
                    'controller' => 'Search',
                    'action'=>'Index',
                    'default_controller' => 'Home',
                    'default_action' => 'Index',
                    'error_controller' => 'Error',
                    'arguments' => ['test' => 'test1']
                ],
                [
                    'controller' => 'search',
                    'test' => 'test1',
                ],
                [
                    [
                        'name' => 'default',
                        'controller' => 'Home',
                        'action' => 'Index',
                        'url' => '{controller}/{action}/',
                        'error_controller' => 'Error'
                    ],
                ]
            ],
            [
                [
                    'controller' => 'Home',
                    'action'=>'Test',
                    'default_controller' => 'Home',
                    'default_action' => 'Index',
                    'error_controller' => 'Error',
                    'arguments' => ['controllers' => 'search']
                ],
                [
                    'controllers' => 'search',
                    'action' => 'test',
                ],
                [
                    [
                        'name' => 'default',
                        'controller' => 'Home',
                        'action' => 'Index',
                        'url' => '{controller}/{action}/',
                        'error_controller' => 'Error'
                    ],
                ]
            ],
            [
                [
                    'controller' => 'Search',
                    'action'=>'Test',
                    'default_controller' => 'Home',
                    'default_action' => 'Index',
                    'error_controller' => 'Error',
                    'arguments' => []
                ],
                [
                    'controller' => 'search',
                    'action' => 'test',
                ],
                null
            ],
        ];
    }

    /**
     * @dataProvider providerUri
     * @covers \Sixx\Router\ForwardRoute
     */
    public function testForwardRoute($need, $get, $map)
    {
        require_once 'RouteMapExample.php';

        if ($map != null) {
            $object = new RouteMapExample;
            $object::setRoutes($map);
        } else
            $object = $map;

        $stub = $this->getMockBuilder('\Sixx\Net\Request')->disableOriginalConstructor()->getMock();
        $stub->get = $get;
        $route = new \Sixx\Router\ForwardRoute($stub, $object);

        $this->assertEquals($need, $route->route());
    }
}
