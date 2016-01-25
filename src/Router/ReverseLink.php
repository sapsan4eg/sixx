<?php

namespace Sixx\Router;

/**
 * Sixx\Router\ReversRoute
 *
 * @package    Sixx
 * @subpackage Router
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class ReverseLink extends AbstractLink
{

    /**
     * @param string $uri
     * @param string $controller
     * @param string $action
     * @param array $arguments
     * @return array
     */
    protected function fillUri($uri = '', $controller = '', $action = '', $arguments = [])
    {
        foreach($this->routes as $values) {
            $it = $this->myRoute($values['url'], $arguments, $uri, $controller, $action);
            if($it['is']) {
                $uri = $it['url'];
                $arguments = $it['arguments'];
                break;
            }
        }

        return ['uri' => $uri, 'arguments' => $arguments];
    }

    /**
     * @param $route
     * @param $arguments
     * @param $url
     * @param $controller
     * @param $action
     * @return array
     */
    protected function myRoute($route, $arguments, $url, $controller, $action)
    {
        if (! ($it = StringWorking::itSameRoute($route, $arguments)))
            return ['url' => $url, 'arguments' => $arguments, 'is' => $it];

        if (strpos($route, '{personal_route}') !== false && ! empty($this->entity)) {
            if (! ($list = $this->entity->listRoutes()))
                return ['url' => $url, 'arguments' => $arguments, 'is' => false];

            $argument = ['key' => 0, 'count' => 0];

            foreach ($list as $key => $value) {
                if ($value['controller'] != $controller || $value['action'] != $action)
                    continue;

                if (isset($value['arguments'])) {
                    $count = count($value['arguments']);
                    if ($count <= count($arguments)) {

                        foreach ($arguments as $keyarg => $valuearg) {
                            if (isset($value['arguments'][$keyarg]))
                                $count = $value['arguments'][$keyarg] == $valuearg ? ($count - 1) : $count;
                        }

                        if ($count <= 0)
                            $argument = $argument['count'] <= count($value['arguments']) ? array('key' => $key, 'count' => count($value['arguments'])) : $argument;
                    }
                } else {
                    $argument = $argument['count'] == 0 ? array('key' => $key, 'count' => 0) : $argument;
                }
            }

            if (empty($list[$argument['key']]))
                return ['url' => $url, 'arguments' => $arguments, 'is' => false];


            $temp = $this->fillLink($route, $url, $action, $controller, empty($list[$argument['key']]['arguments']) ? [] : $list[$argument['key']]['arguments'], $arguments, $argument['key']);
            $url = $temp['url'];
            $arguments = $temp['arguments'];
            unset($temp);


        } elseif (count(StringWorking::map($route)) - 3 <= count($arguments)) {

            $temp = $this->fillLink($route, $url, $action, $controller, $arguments, $arguments);
            $url = $temp['url'];
            $arguments = $temp['arguments'];
            unset($temp);
        }

        return ['url' => $url, 'arguments' => $arguments, 'is' => $it];
    }

    /**
     * @param string $route
     * @param string $url
     * @param string $action
     * @param string $controller
     * @param array $arguments
     * @param string $personal
     * @return array
     */
    protected function fillLink($route = '', $url = '', $action = '', $controller = '', $argument = [], $arguments = [], $personal = '')
    {
        $url .= str_ireplace(['{action}', '{controller}', '{personal_route}',], [$action, $controller, $personal], $route);

        $needDelArray = [];

        foreach ($argument as $key => $value) {
            if (strpos($url, '{' . $key . '}') !== false) {
                $url = str_replace('{' . $key . '}', $value, $url);
                $needDelArray[] = $key;
            }
        }

        foreach ($needDelArray as $value) {
            unset($arguments[$value]);
        }

        return ['url' => $url, 'arguments' => $arguments];
    }
}
