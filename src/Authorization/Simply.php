<?php

namespace Sixx\Authorization;

use Sixx\Router;
use Sixx\Net\Request;

/**
 * Sixx\Authorization\Simply
 *
 * @package    Sixx
 * @subpackage Net
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class Simply implements AuthorizationInterface
{

    /**
     * Authorization data
     * @var array
     */
    protected $identity = [
        'isAuthenticated' => null,
        'name' => null,
        'email' => null,
        'id' => null
    ];

    /**
     * @var EntityInterface
     */
    protected $entity;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Router
     */
    protected $router;

    /**
     * Simply constructor.
     * @param EntityInterface $entity
     * @param Router $router
     * @param Request $request
     */
    public function __construct(EntityInterface $entity, Router $router, Request $request)
    {
        $this->entity = $entity;
        $this->request = $request;
        $this->router = $router;
        $this->checkAccess();
    }

    /**
     * Checking access to part of system
     *
     * @access protected
     * @return void
     */
    protected function checkAccess()
    {
        $havePermission = true;
        $userId = 0;

        if (!empty($this->request->session['user']) && !empty($this->request->session['user_id'])) {
            if (($user = $this->entity->getUserByName($this->request->session['user'])) && ($user['id'] == $this->request->session['user_id'] && $user['status'] == 1)) {
                $this->identity['isAuthenticated'] = true;
                $this->identity['name'] = $user['name'];
                $this->identity['email'] = $user['email'];
                $this->identity['id'] = $user['id'];
                $userId = $user['id'];
            } else {
                $this->logout();
            }
        }

        if (($aut = $this->entity->getPermission($this->router->getController()))) {
            $havePermission = $this->havePermission($aut, $this->router->getAction(), $userId);
        }

        if (false == $havePermission) {
            $this->request->session['authorization']['came'] = ['controller' => $this->router->getController(), 'action' => $this->router->getAction(), 'arguments' => $this->router->getArguments()];
            throw new NotHavePermissionException('User don\'t have permissions.');
        }
    }

    /**
     * Try sign in system
     *
     * @access public
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login($username = '', $password = '')
    {
        if (($user = $this->entity->getUserByNamePwd($username, $password))) {
            $this->request->session['user'] = $user['email'];
            $this->request->session['user_id'] = $user['id'];
            return true;
        }

        return false;
    }

    /**
     * Log out from system
     *
     * @access  public
     * @return	void
     */
    public function logout()
    {
        if (isset($this->request->session['user'])) {
            unset($this->request->session['user']);
        }

        if (isset($this->request->session['user_id'])) {
            unset($this->request->session['user_id']);
        }
    }

    /**
     * Check have user permission
     *
     * @access protected
     * @param array	$permissionArray
     * @param string $actionName
     * @param int $userId
     * @return bool
     */
    protected function havePermission(array $permissionArray, $actionName, $userId)
    {
        $permission = true;

        if (!empty($permissionArray['actions']) && !empty($permissionArray['actions'][$actionName])) {
            $permission = $this->comparisonPermissions($permissionArray['actions'][$actionName], $userId);
        } elseif (!empty($permissionArray['controller'])) {
            $permission = $this->comparisonPermissions ($permissionArray['controller'], $userId);
        }

        return $permission;
    }

    /**
     * Check comparison user permission
     *
     * @access protected
     * @param array	$permissions
     * @param int $userId
     * @return bool
     */
    protected function comparisonPermissions ($permissions, $userId)
    {
        $permission = true;

        if (is_array($permissions)) {
            $notNeedRoles = false;

            if (!empty($permissions['users']) && is_array($permissions['users'])) {
                $notNeedRoles = (bool)array_search($userId, $permissions['users']);
            }

            if (false == $notNeedRoles) {
                $permission = false;
                if (!empty($permissions['roles']) && is_array($permissions['roles']) && $userId > 0) {
                    foreach ($permissions['Roles'] as $role) {
                        if (($role = $this->entity->getRoleByName($role)) && $this->entity->haveUserInRole($userId, $role['id'])) {
                            $permission = true;
                            break;
                        }
                    }
                }
            }
        } elseif ($permissions == 'authorize' && $userId == 0) {
            $permission = false;
        }

        return $permission;
    }

    /**
     * Registration users
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    public function registration($username, $password)
    {
        if ($this->entity->getUserByName($username)) {
            return ['answer' => 'error', 'description' => 'have_that_user'];
        }

        if (!(bool)$this->entity->addUser($username, $password)) {
            return ['answer' => 'error', 'description' => 'cannot_add_user'];
        }

        $token = $this->getToken($username);

        return ['answer' => 'success', 'description' => $token, 'name' => $username];
    }

    /**
     * Enable users
     *
     * @param string $username
     * @param string $token
     * @return array
     */
    public function enableUser($username, $token)
    {
        if (!($user = $this->entity->getUserByName($username))) {
            return ['answer' => 'error', 'description' => 'havent_that_user'];
        }

        if ($user['status'] == 1) {
            return ['answer' => 'success', 'description' => 'user_already_enabled'];
        }

        if (!($token_db = $this->entity->getToken($username))) {
            return ['answer' => 'error', 'description' => 'havent_token'];
        } elseif ($token_db != $token) {
            return ['answer' => 'error', 'description' => 'token_dosent_much'];
        }

        $user['status'] = 1;

        $this->entity->enableUser($user['id']);
        $this->entity->deleteToken($username);

        $this->request->session['user'] = $user['email'];
        $this->request->session['user_id'] = $user['id'];

        $this->identity['isAuthenticated'] = true;
        $this->identity['name'] = $user['email'];
        $this->identity['id'] = $user['id'];

        return ['answer' => 'success', 'description' => 'user_success_enabled'];
    }

    /**
     * Generate token
     *
     * @param string $username
     * @return mixed
     */
    public function getToken($username)
    {
        if (($user = $this->entity->getUserByName($username))) {
            if (($token = $this->entity->getToken($username))) {
                return $token;
            }

            $this->addToken($user);
            return $this->getToken($username);
        }

        return false;
    }

    /**
     * add token
     *
     * @param array $user
     * @return mixed
     */
    protected function addToken($user)
    {
        $token = $this->generateToken($user);
        $this->entity->addToken($user['email'], $token);
    }

    /**
     * generate token
     *
     * @param array $user
     * @return mixed
     */
    protected function generateToken($user)
    {
        return md5($user['id'] . $user['email'] . date("y-m-d H:i:s"));
    }

    /**
     * change password
     *
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function changePassword($userId, $password)
    {
        if (($user = $this->entity->getUserById($userId))) {
            return (bool)$this->entity->changePassword($userId, $password);
        }

        return false;
    }

    public function identity()
    {
        return $this->identity;
    }
}
