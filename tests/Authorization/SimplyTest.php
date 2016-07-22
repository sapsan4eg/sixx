<?php

class SimplyTest extends PHPUnit_Framework_TestCase
{
    public function getEntity()
    {
        return [
            ['Home', 'Index', ['controller' => 'authorize'], []],
            ['Home', 'Index', ['controller' => 'allowAnonymous', 'actions' => ['Index' => 'authorize'], ['Any' => '']], []],
            ['Home', 'Index', ['controller' => 'authorize'], []],
        ];
    }

    /**
     * @dataProvider getEntity
     * @expectedException \Sixx\Authorization\NotHavePermissionException
     * @expectedExceptionMessage User don't have permissions.
     */
    public function testException($controller, $method, $return, $session)
    {
        $router = $this->getMockBuilder('Sixx\\Router')->disableOriginalConstructor()->getMock();
        $router->method('getController')->willReturn($controller);
        $router->method('getAction')->willReturn($method);
        $request = $this->getMockBuilder('Sixx\\Net\\Request')->disableOriginalConstructor()->getMock();
        $request->session = $session;
        $entity = $this->getMockBuilder('Sixx\\Authorization\\EntityInterface')->getMock();
        $entity->method('getPermission')->willReturn($return);
        $entity->method('getUserByName')->willReturn(['email' => 'hello@world.com', 'id' => 1, 'status' => 1]);

        new Sixx\Authorization\Simply($entity, $router, $request);

    }

    public function identityProvider()
    {
        return [
            [
                'Home',
                'Index',
                ['user' => 'info@six-x.org', 'user_id' => 12],
                ['isAuthenticated' => true, 'name' => 'Yuri Nasyrov', 'email' => 'info@six-x.org', 'id' => 12],
                ['email' => 'info@six-x.org', 'name' => 'Yuri Nasyrov', 'id' => 12, 'status' => 1],
            ],
            [
                'Home',
                'Index',
                ['user' => 'info@six-x.org', 'user_id' => 12],
                ['isAuthenticated' => false, 'name' => null, 'email' => null, 'id' => null],
                false,
            ],
        ];
    }

    /**
     * @dataProvider identityProvider
     * @param $controller
     * @param $method
     * @param $session
     * @param $expected
     * @param $returnUser
     */
    public function testIdentity($controller, $method, $session, $expected, $returnUser)
    {
        $router = $this->getMockBuilder('Sixx\\Router')->disableOriginalConstructor()->getMock();
        $router->method('getController')->willReturn($controller);
        $router->method('getAction')->willReturn($method);
        $request = $this->getMockBuilder('Sixx\\Net\\Request')->disableOriginalConstructor()->getMock();
        $request->session = $session;
        $entity = $this->getMockBuilder('Sixx\\Authorization\\EntityInterface')->getMock();
        $entity->method('getUserByName')->willReturn($returnUser);

        $authorization = new Sixx\Authorization\Simply($entity, $router, $request);
        $this->assertEquals($expected, $authorization->identity());
    }

    public function getEntityArray()
    {
        return [
            ['Home', 'Index', ['argument' => 'some'], ['controller' => 'authorize'], ['controller' => 'Home', 'action' => 'Index', 'arguments' => ['argument' => 'some']]],
            ['Home', 'Index', [], ['controller' => 'allowAnonymous', 'actions' => ['Index' => 'authorize'], ['Any' => '']], ['controller' => 'Home', 'action' => 'Index', 'arguments' =>[]]],
            ['Home', 'Index', [], ['controller' => 'authorize'], ['controller' => 'Home', 'action' => 'Index', 'arguments' =>[]]],
        ];
    }

    /**
     * @dataProvider getEntityArray
     */
    public function testNotHavePermissions($controller, $method, $argument, $return, $expected)
    {
        $router = $this->getMockBuilder('Sixx\\Router')->disableOriginalConstructor()->getMock();
        $router->method('getController')->willReturn($controller);
        $router->method('getAction')->willReturn($method);
        $router->method('getArguments')->willReturn($argument);
        $request = $this->getMockBuilder('Sixx\\Net\\Request')->disableOriginalConstructor()->getMock();
        $request->session = [];
        $entity = $this->getMockBuilder('Sixx\\Authorization\\EntityInterface')->getMock();
        $entity->method('getPermission')->willReturn($return);
        $entity->method('getUserByName')->willReturn(['email' => 'hello@world.com', 'id' => 1, 'status' => 1]);

        try {
            new Sixx\Authorization\Simply($entity, $router, $request);
        } catch (\Exception $e) {
            $this->assertEquals($expected, $request->session['authorization']['came']);
        }
    }

}
