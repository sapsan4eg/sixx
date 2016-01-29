<?php

namespace Sixx\Autorization;

/**
 * Sixx\Autorization\EntityInterface
 *
 * @package    Sixx
 * @subpackage Router
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
interface EntityInterface
{
    /**
     * @param string $hash
     * @return array|bool
     */
    public function getUserByHash($hash);

    /**
     * @param string $username
     * @return array|bool
     */
    public function getUserByName($username);

    /**
     * @param string $controller
     * @return mixed
     */
    public function getPermission($controller);

    /**
     * @param string $roleName
     * @return array|bool
     */
    public function getRoleByName($roleName);

    /**
     * @param $userId
     * @param $roleId
     * @return bool
     */
    public function haveUserInRole($userId, $roleId);

    /**
     * @param array $user
     * @return string
     */
    public function userHash($user);

    /**
     * @param string $username
     * @param string $pwd
     * @return array|bool
     */
    public function getUserByNamePwd($username, $pwd);

    /**
     * @param string $username
     * @param string $password
     * @return int
     */
    public function addUser($username, $password);

    /**
     * @param string $username
     * @return string
     */
    public function getToken($username);

    /**
     * @param array $user
     * @return bool
     */
    public function upUser($user);

    /**
     * @param string $username
     * @return bool mixed
     */
    public function delToken($username);

    /**
     * @param string $username
     * @param string $token
     * @return bool
     */
    public function addToken($username, $token);

    /**
     * @param int $id
     * @return array|bool
     */
    public function getUserById($id);
}