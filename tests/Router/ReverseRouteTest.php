<?php
class ReverseRouteTest extends PHPUnit_Framework_TestCase
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
                    'controller' => 'tort',
                    'test' => 'test1',
                    '_route_' => 'Search/Index',
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
                    'action'=>'Index',
                    'default_controller' => 'Home',
                    'default_action' => 'Index',
                    'error_controller' => 'Error',
                    'arguments' => ['test' => 'test1']
                ],
                [
                    'controller' => 'tort',
                    'test' => 'test1',
                    '_route_' => 'Search',
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
                    'controller' => 'Error',
                    'action'=>'Index',
                    'default_controller' => 'Home',
                    'default_action' => 'Index',
                    'error_controller' => 'Error',
                    'arguments' => []
                ],
                [
                    'controller' => 'tort',
                    '_route_' => 'Search/Cool/Sets',
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
                    'action'=>'Subscriptions',
                    'default_controller' => 'Test',
                    'default_action' => 'Notfound',
                    'error_controller' => 'Error',
                    'arguments' => ['test' => 'test1', 'year' => '2015']
                ],
                [
                    'controller' => 'tort',
                    'test' => 'test1',
                    '_route_' => 'Subscriptions/Search/2015',
                ],
                [
                    [
                        'name' => 'custom',
                        'controller' => 'Test',
                        'action' => 'Notfound',
                        'url' => '{action}/{controller}/{year}',
                        'error_controller' => 'Error'
                    ],
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
                    'action'=>'Subscriptions',
                    'default_controller' => 'Test',
                    'default_action' => 'Notfound',
                    'error_controller' => 'Error',
                    'arguments' => ['test' => 'test1', 'year' => '2015']
                ],
                [
                    'controller' => 'tort',
                    'test' => 'test1',
                    '_route_' => 'subscriptions.php/2015',
                ],
                [
                    [
                        'name' => 'custom',
                        'controller' => 'Test',
                        'action' => 'Notfound',
                        'url' => '{personal_route}.php/{year}',
                        'error_controller' => 'Error'
                    ],
                    [
                        'name' => 'default',
                        'controller' => 'Home',
                        'action' => 'Index',
                        'url' => '{controller}/{action}/',
                        'error_controller' => 'Error'
                    ],
                ],
                [
                    'controller' => 'Search',
                    'action'=>'Subscriptions',

                ]
            ],
            [
                [
                    'controller' => 'Subscriptions.php',
                    'action' => 'Index',
                    'default_controller' => 'Home',
                    'default_action' => 'Index',
                    'error_controller' => 'Error',
                    'arguments' => ['test' => 'test1']
                ],
                [
                    'controller' => 'tort',
                    'test' => 'test1',
                    '_route_' => 'subscriptions.php',
                ],
                [
                    [
                        'name' => 'custom',
                        'controller' => 'Test',
                        'action' => 'Notfound',
                        'url' => '{personal_route}.php',
                        'error_controller' => 'Error'
                    ],
                    [
                        'name' => 'default',
                        'controller' => 'Home',
                        'action' => 'Index',
                        'url' => '{controller}/{action}/',
                        'error_controller' => 'Error'
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerUri
     * @covers \Sixx\Router\ReverseRoute
     */
    public function testReverseRoute($need, $get, $map, $entitys = null)
    {
        require_once 'RouteMapExample.php';

        if ($map != null) {
            $object = new RouteMapExample;
            $object::setRoutes($map);
        } else
            $object = $map;

        $stub = $this->getMockBuilder('\Sixx\Net\Request')->disableOriginalConstructor()->getMock();
        $stub->get = $get;

        $entity = $this->getMockBuilder('\Sixx\Router\EntityInterface')->getMock();
        $entity->method('getRoute')->willReturn($entitys);

        $route = new \Sixx\Router\ReverseRoute($stub, $object, $entity);

        $this->assertEquals($need, $route->route());
    }
}
