<?php

namespace Sixx\Autorization;

/**
 * Sixx\Autorization\Simply
 *
 * @package    Sixx
 * @subpackage Net
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
interface AutorizationInterface
{
    /**
     * AutorizationInterface constructor.
     * @param EntityInterface $entity
     * @param \Sixx\Router\AbstractLink $router
     * @param \Sixx\Net\Request $request
     */
    public function __construct(EntityInterface $entity, \Sixx\Router\AbstractLink $router, \Sixx\Net\Request $request);

    /**
     * @param string $username
     * @param string $password
     * @return mixed
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
     * @param string $mail
     * @param string $token
     * @return array
     */
    public function enableUser($mail, $token);

    /**
     * @param string $mail
     * @return bool|string
     */
    public function getToken($mail);

    /**
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function changePassword($userId, $password);
}
