<?php

namespace Sixx\Authorization;

/**
 * Sixx\Authorization\EntityInterface
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
     * @param string $username
     * @param string $password
     * @return int
     */
    public function addUser($username, $password);

    /**
     * @param int $id
     * @return array|bool
     */
    public function getUserById($id);

    /**
     * @param string $username
     * @return array|bool
     */
    public function getUserByName($username);

    /**
     * @param string $username
     * @param string $pwd
     * @return array|bool
     */
    public function getUserByNamePwd($username, $pwd);

    /**
     * @param string $roleName
     * @return array|bool
     */
    public function getRoleByName($roleName);

    /**
     * @param string $controller
     * @return mixed
     */
    public function getPermission($controller);

    /**
     * @param $userId
     * @param $roleId
     * @return bool
     */
    public function haveUserInRole($userId, $roleId);

    /**
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function changePassword($userId, $password);

    /**
     * @param int $userId
     * @return bool
     */
    public function enableUser($userId);

    /**
     * @param string $username
     * @param string $token
     * @return bool
     */
    public function addToken($username, $token);

    /**
     * @param string $username
     * @return string
     */
    public function getToken($username);

    /**
     * @param string $username
     * @return bool mixed
     */
    public function deleteToken($username);
}
