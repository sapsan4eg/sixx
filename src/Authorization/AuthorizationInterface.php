<?php

namespace Sixx\Authorization;

use Sixx\Router;
use Sixx\Net\Request;

interface AuthorizationInterface
{
    /**
     * AuthorizationInterface constructor.
     * @param EntityInterface $entity
     * @param Router$router
     * @param Request $request
     */
    public function __construct(EntityInterface $entity, Router $router, Request $request);

    /**
     * @param string $username
     * @param string $password
     * @return true
     */
    public function login($username = '', $password = '');

    /**
     * @return mixed
     */
    public function logout();

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    public function registration($username, $password);

    /**
     * @param string $username
     * @param string $token
     * @return array
     */
    public function enableUser($username, $token);

    /**
     * @param string $username
     * @return bool|string
     */
    public function getToken($username);

    /**
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function changePassword($userId, $password);

    /**
     * User identity
     *
     * @return array
     */
    public function identity();
}
