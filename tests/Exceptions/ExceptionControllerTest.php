<?php

class ExceptionControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Sixx\Exceptions\ExceptionController::Error
     */
    public function testError()
    {
        $request = $this->getMockBuilder('Sixx\\Net\\Request')->disableOriginalConstructor()->getMock();
        $router = $this->getMockBuilder('Sixx\\Router')->disableOriginalConstructor()->getMock();

        $controller = new \Sixx\Exceptions\ExceptionController($request, new \Sixx\View($router), $router);

        $view = $controller->Error(500, 'hello');
        $file = file_get_contents(__DIR__ . '/../../src/Html/500.html');
        $this->assertEquals(500, $view->getHeaders()['status']);
        $this->assertEquals($file, $view->getContent());

        $view = $controller->Error(404, 'hello');
        $file = file_get_contents(__DIR__ . '/../../src/Html/404.html');
        $this->assertEquals(404, $view->getHeaders()['status']);
        $this->assertEquals($file, $view->getContent());

        $view = $controller->Error(501, 'hello');
        extract(['code' => 501, 'message' => 'hello']);
        ob_start();
        include(__DIR__ . '/../../src/Html/error.html');
        $file = ob_get_contents();
        ob_end_clean();
        $this->assertEquals(501, $view->getHeaders()['status']);
        $this->assertEquals($file, $view->getContent());

        require_once __DIR__ . '/TestClass.php';
        $router->method('getErrorController')->willReturn('Exception');
        $router->method('getDefaultAction')->willReturn('Error');
        $router->method('link')->willReturn('some_url');
        $view = $controller->Error(501, 'hello');
        $this->assertEquals(302, $view->getHeaders()['status']);
        $this->assertEquals('some_url', $view->getHeaders()['Location']);
        $this->assertEquals([501, 'hello'], $request->session['error']);
    }

    /**
     * @covers Sixx\Exceptions\ExceptionController::JsonError
     */
    public function testJsonError()
    {
        $request = $this->getMockBuilder('Sixx\\Net\\Request')->disableOriginalConstructor()->getMock();
        $router = $this->getMockBuilder('Sixx\\Router')->disableOriginalConstructor()->getMock();

        $controller = new \Sixx\Exceptions\ExceptionController($request, new \Sixx\View($router), $router);

        $view = $controller->JsonError(500, ['message' => 'hello']);

        $this->assertEquals(500, $view->getHeaders()['status']);
        $this->assertEquals('{"message":"hello"}', $view->getContent());
    }

    /**
     * @covers Sixx\Exceptions\ExceptionController::RedirectError
     */
    public function testRedirectError()
    {
        $request = $this->getMockBuilder('Sixx\\Net\\Request')->disableOriginalConstructor()->getMock();
        $router = $this->getMockBuilder('Sixx\\Router')->disableOriginalConstructor()->getMock();
        $controller = new \Sixx\Exceptions\ExceptionController($request, new \Sixx\View($router), $router);
        $router->method('link')->willReturn('some_url');
        $view = $controller->RedirectError('Account', 'Index',[]);
        $this->assertEquals(302, $view->getHeaders()['status']);
        $this->assertEquals('some_url', $view->getHeaders()['Location']);
    }
}
