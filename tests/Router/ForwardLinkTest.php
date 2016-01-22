<?php

class ForwardLinkTest extends PHPUnit_Framework_TestCase
{

    public function providerLink()
    {
        return [
            [
                'http://php.net/?controller=Search&action=Index&test=cool',
                'http://php.net/',
                [
                    'controller' => 'Search',
                    'action' => 'Test',
                    'default_controller' => 'Home',
                    'default_action' => 'Index',
                    'error_controller' => 'Error',
                    'arguments' => []
                ],
                '',
                '',
                ['test' => 'cool']
            ],
            [
                'http://php.net/?controller=Some&action=Hell&test=cool',
                'http://php.net/',
                [
                    'controller' => 'Search',
                    'action' => 'Test',
                    'default_controller' => 'Home',
                    'default_action' => 'Index',
                    'error_controller' => 'Error',
                    'arguments' => []
                ],
                'Hell',
                'Some',
                ['test' => 'cool']
            ],
        ];
    }

    public function providerSource()
    {
        return [
            [
                'http://php.net/cool/server/my_png.img',
                ['HTTP_HOST' => 'php.net'],
                'http://php.net/cool/server',
                'my_png.img',
            ],
            [
                'http://www.six-x.org/img/test.png',
                ['HTTP_HOST' => 'www.six-x.org'],
                'http://php.net/',
                'img/test.png',
            ],
            [
                'http://six-x.org/img/test.png',
                ['HTTP_HOST' => 'six-x.org'],
                '',
                'img/test.png',
            ],
        ];
    }

    /**
     * @dataProvider providerLink
     * @covers Sixx\Router\ForwardLink::link
     */
    public function testLink($need = '', $url = '', $route =[], $action = '', $controller = '', $arguments = [])
    {
        $uri = parse_url($url);
        $stub = $this->getMockBuilder('\Sixx\Router\RouteInterface')->getMock();
        $stub->method('uri')->willReturn($uri);
        $stub->method('serverPath')->willReturn('');
        $stub->method('route')->willReturn($route);
        $route = new \Sixx\Router\ForwardLink($stub);

        $this->assertEquals($need, $route->link($action, $controller, $arguments));
    }

    /**
     * @dataProvider providerSource
     * @covers Sixx\Router\AbstractLink::source
     */
    public function testSource($need, array $server, $serverPath = '', $name = '')
    {
        $this->assertEquals($need, \Sixx\Router\ForwardLink::source($server, $serverPath, $name));
    }
}