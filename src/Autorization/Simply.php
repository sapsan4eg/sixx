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
class Simply implements AutorizationInterface
{

    /**
     * Autorization data
     *
     * @var array
     */
    public $identity = [
        'isAuthenticated' => null,
        'name' => null,
        'email' => null,
        'id' => null
    ];
    protected $havePermission = true;
    protected $link = '';
    protected $cookieKey = 'erSDf3410-fX3s:xs]Q/.czk%*a{Vrelz;45a?';
    protected $entity;
    protected $request;

    /**
     * Constructor
     *
     * @param	object  $storage
     * @param   array   $route
     */
    public function __construct(EntityInterface $entity, \Sixx\Router\AbstractLink $router, \Sixx\Net\Request $request)
    {
        $this->entity = $entity;
        $this->request = $request;
        $this->router = $router;
        $this->checkAccess();
    }

    /**
     * Checking access to part of system
     *
     * @access  protected
     * @return	void
     */
    protected function checkAccess()
    {
        if (! empty($this->request->cookie['hash']) && ! empty($this->request->cookie['dfp'])
            && empty($this->request->session->data['user']) && empty($this->request->session->data['user_id'])
        ) {
            if (($user = $this->entity->getUserByHash($this->request->cookie['hash']))
                && ($dfp = $this->dfp($user)) && $dfp == $this->request->cookie['dfp']) {
                $this->request->session->data['user'] 	= $user['email'];
                $this->request->session->data['user_id'] = $user['id'];
                setcookie('hash', $this->request->cookie['hash'], time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
                setcookie('dfp', $this->request->cookie['dfp'], time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
            } else {
                setcookie('hash', null, -1, '/', $this->request->server['HTTP_HOST']);
                setcookie('dfp', null, -1, '/', $this->request->server['HTTP_HOST']);
            }
        }

        $this->havePermission = true;
        $userId = 0;

        if (! empty($this->request->session->data['user']) && ! empty($this->request->session->data['user_id'])) {
            if (($user = $this->entity->getUserByName($this->request->session->data['user']))
                && ($user['id'] == $this->request->session->data['user_id'] && $user['status'] == 1))
            {
                $this->identity['isAuthenticated'] = true;
                $this->identity['name'] = $user['email'];
                $this->identity['id'] = $user['id'];
                $userId = $user['id'];

                if (empty($this->request->cookie['hash']) && empty($this->request->cookie['dfp'])) {
                    setcookie('hash', $this->entity->userHash($user), time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
                    setcookie('dfp', $this->dfp($user), time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
                }

            } else {
                $this->logout();
            }
        }

        if(($aut = $this->entity->getPermission($this->router->route()['controller']))) {
            $this->havePermission = $this->havePermission($aut, $this->router->route(), $userId);
        }

        if($this->havePermission == false) {
            $this->request->session->data['message'] = ['warning', 'warning', 'error_not_have_perm'];
            $came = ['controller' => $this->router->route()['controller'], 'action' => $this->router->route()['action'],];

            if(count($this->router->route()['arguments']) > 0)
                $came['arguments'] = $this->router->route()['arguments'];

            $this->link = $this->router->link('Login', 'Account', ['came_from' => json_encode($came)]);
        }
    }

    /**
     * dfp
     */
    protected function dfp($user)
    {
        return md5($user['id'] . $this->cookieKey . $user['email']
            . (! empty($this->request->server['HTTP_ACCEPT']) ? $this->request->server['HTTP_ACCEPT'] : '')
            . (! empty($this->request->server['HTTP_USER_AGENT']) ? $this->request->server['HTTP_USER_AGENT'] : '')
           # . (! empty($this->request->server['HTTP_ACCEPT_LANGUAGE']) ? $this->request->server['HTTP_ACCEPT_LANGUAGE'] : '')
        );
    }

    /**
     * Try sign in system
     *
     * @access  public
     * @param	string	$username
     * @param   string  $password
     * @return	bool
     */
    public function login($username = '', $password = '')
    {
        \Sixx\Log\Logger::info(json_encode(['action'=> 'try enter', 'user' => $username, 'password' => \Sixx\Protection\Protector::escape($password)]), ['PROGRAM' => 'autorization/login']);

        if(($user = $this->entity->getUserByNamePwd(strtolower(\Sixx\Protection\Protector::xssClean($username)), $password))) {
            $this->request->session->data['user'] 	 = $user['email'];
            $this->request->session->data['user_id'] = $user['id'];

            \Sixx\Log\Logger::info(json_encode(['action'=> 'success enter', 'user' => $username]), ['PROGRAM' => 'autorization/login']);
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
        \Sixx\Log\Logger::info(json_encode(['action'=> 'logout', 'user' => isset($this->request->session->data['user']) ? $this->request->session->data['user'] : 'not understand']), ['PROGRAM' => 'autorization/logout']);

        if(isset($this->request->session->data['user']))
            unset($this->request->session->data['user']);

        if(isset($this->request->session->data['user_id']))
            unset($this->request->session->data['user_id']);

        setcookie('hash', null, -1, '/', $this->request->server['HTTP_HOST']);
        setcookie('dfp', null, -1, '/', $this->request->server['HTTP_HOST']);
    }

    /**
     * Check have user permission
     *
     * @access protected
     * @param array	$permissionArray
     * @param array $route
     * @param int $userId
     * @return bool
     */
    protected function havePermission(array $permissionArray, array $route, $userId)
    {
        $permission = true;

        if(! empty($permissionArray['actions']) && ! empty($permissionArray['actions'][$route['action']]))
            $permission = $this->comparisonPermissions($permissionArray['actions'][$route['action']], $userId);
        elseif( ! empty($permissionArray['controller']))
            $permission = $this->comparisonPermissions ($permissionArray['controller'], $userId);

        return $permission;
    }

    /**
     * Check comparison user permission
     *
     * @access  protected
     * @param	array	$permissions
     * @param   int     $userId
     * @return	bool
     */
    protected function comparisonPermissions ($permissions, $userId)
    {
        $permission = true;

        if(is_array($permissions)) {
            $notneedRoles = false;

            if(! empty($permissions['Users']) && is_array($permissions['Users']))
                $notneedRoles = (bool)array_search($userId, $permissions['Users']);

            if($notneedRoles == false) {
                $permission = false;

                if( ! empty($permissions['Roles']) && is_array($permissions['Roles']) && $userId > 0) {
                    foreach($permissions['Roles'] As $role) {
                        if(($role = $this->entity->getRoleByName($role))
                            && $this->entity->haveUserInRole($userId, $role['id'])) {
                            $permission = true;
                            break;
                        }
                    }
                }
            }
        } elseif($permissions == 'Autorize' && $userId == 0) {
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
        $username = \Sixx\Protection\Protector::clean(strtolower($username));

        if (! filter_var($username, FILTER_VALIDATE_EMAIL))
            return ['answer' => 'error', 'description' => 'not_valid_email'];

        if ($this->entity->getUserByName($username))
            return ['answer' => 'error', 'description' => 'have_that_user'];

        if (! (bool)$this->entity->addUser($username, $password))
            return ['answer' => 'error', 'description' => 'cannot_add_user'];

        $token = $this->getToken($username);

        return ['answer' => 'success', 'description' => $token, 'name' => $username];
    }

    /**
     * Enable users
     *
     * @param string $mail
     * @param string $token
     * @return array
     */
    public function enableUser($username, $token)
    {
        if(! filter_var($username, FILTER_VALIDATE_EMAIL))
            return ['answer' => 'error', 'description' => 'not_valid_email'];

        if(! ($user = $this->entity->getUserByName($username)))
            return ['answer' => 'error', 'description' => 'havent_that_user'];

        if($user['status'] == 1)
            return ['answer' => 'success', 'description' => 'user_already_enabled'];

        if(! ($token_db = $this->entity->getToken($username)))
            return ['answer' => 'error', 'description' => 'havent_token'];

        if($token_db != $token)
            return ['answer' => 'error', 'description' => 'token_dosent_much'];

        $user['status'] = 1;

        $this->entity->upUser($user);
        $this->entity->delToken($username);

        $this->request->session->data['user'] 	= $user['email'];
        $this->request->session->data['user_id'] = $user['id'];

        $this->identity['isAuthenticated'] = true;
        $this->identity['name'] = $user['email'];
        $this->identity['id'] = $user['id'];
        \Sixx\Log\Logger::info(json_encode(array('action'=> 'success enable', 'user' => $user['email'])), ['PROGRAM' => 'autorization/enable']);

        return ['answer' => 'success', 'description' => 'user_success_enabled'];
    }

    /**
     * Generate token
     *
     * @param string $mail
     * @return mixed
     */
    public function getToken($username)
    {
        if(($user = $this->entity->getUserByName($username))) {
            if(($token = $this->entity->getToken($username)))
                return $token;

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
            $user['password'] = md5($password);
            return (bool)$this->entity->upUser($user);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function havesPermission()
    {
        return $this->havePermission;
    }

    /**
     * @return string
     */
    public function link()
    {
        return $this->link;
    }

    public function identity()
    {
        return $this->identity;
    }
}
