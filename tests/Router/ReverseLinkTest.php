<?php

class ReverseLinkTest extends PHPUnit_Framework_TestCase
{
    public function providerLink()
    {
        return [
            [
                'http://php.net/Home/Index/',
                'http://php.net/',
                [
                    'name' => 'default',
                    'controller' => 'Home',
                    'action' => 'Index',
                    'default_action' => 'Index',
                    'url' => '{controller}/{action}/',
                    'error_controller' => 'Error'
                ],
                'Index',
                'Home',
                [],
                [
                   [
                        'name' => 'default',
                        'controller' => 'Home',
                        'action' => 'Index',
                        'url' => '{controller}/{action}/',
                        'error_controller' => 'Error'
                    ],
                ],
                ['_route_' => 'Test/Index']
            ],
            [
                'http://php.net/Index/Test/2015',
                'http://php.net/',
                [
                    'name' => 'custom',
                    'controller' => 'Test',
                    'action' => 'Notfound',
                    'default_action' => 'Index',
                    'url' => '{action}/{controller}/{year}',
                    'error_controller' => 'Error'
                ],
                '',
                '',
                ['year' => '2015'],
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
                ],
                ['_route_' => 'Index/Home/2015'],
            ],
            [
                'http://php.net/from_perm_with_love.html',
                'http://php.net/',
                [
                    'name' => 'default',
                    'controller' => 'Home',
                    'action' => 'Index',
                    'default_action' => 'Index',
                    'url' => '{controller}/{action}/',
                    'error_controller' => 'Error'
                ],
                'someAction',
                'Test',
                [],
                [
                    [
                        'name' => 'custom',
                        'controller' => 'Test',
                        'action' => 'Notfound',
                        'url' => '{personal_route}.html',
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
                ['_route_' => 'Test/Index'],
                [
                    'from_perm_with_love' => ['controller' => 'Test', 'action' => 'someAction'],
                ],
            ],
            [
                'http://php.net/from_perm_with_love.html',
                'http://php.net/',
                [
                    'name' => 'custom',
                    'controller' => 'Test',
                    'action' => 'Notfound',
                    'default_action' => 'Index',
                    'url' => '{controller}/{action}/{personal_route}.html',
                    'error_controller' => 'Error'
                ],
                'someAction',
                'Test',
                [],
                [
                    [
                        'name' => 'custom_min',
                        'controller' => 'Test',
                        'action' => 'Notfound',
                        'url' => '{personal_route}.html',
                        'error_controller' => 'Error'
                    ],
                    [
                        'name' => 'custom',
                        'controller' => 'Test',
                        'action' => 'Notfound',
                        'url' => '{controller}/{action}/{personal_route}.html',
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
                ['_route_' => 'Test/Index'],
                [
                    'from_perm_with_love' => ['controller' => 'Test', 'action' => 'someAction'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerLink
     * @covers Sixx\Router\ForwardRoute::link
     */
    public function testLink($need = '', $url = '', $route =[], $action = '', $controller = '', $arguments = [], $routes = [], $get = [], $entitys = null)
    {
        $uri = parse_url($url);
        $stub = $this->getMockBuilder('\Sixx\Router\RouteInterface')->getMock();
        $stub->method('uri')->willReturn($uri);
        $stub->method('serverPath')->willReturn('');
        $stub->method('route')->willReturn($route);
        $stub->method('listRoutes')->willReturn($routes);
        $entity = $this->getMockBuilder('\Sixx\Router\EntityInterface')->getMock();
        $entity->method('listRoutes')->willReturn($entitys);
        $route = new \Sixx\Router\ReverseLink($stub, $entity);

        $this->assertEquals($need, $route->link($action, $controller, $arguments));
    }
}
