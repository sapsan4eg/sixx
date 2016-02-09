<?php

namespace Sixx\Router;

/**
 * Sixx\Router\ReversRoute
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
class ReverseRoute extends AbstractRoute
{
    /**
     * @param array $get
     * @return null
     */
    protected function setUp($get = [])
    {
        $this->direction = self::$REVERSE;

        $route = explode('/', StringWorking::clearUri($get['_route_']));
        $num = $this->getNumRoute($route);
        $array = $this->routeValues($this->routes[$num]['url'], $route, $num);

        if(count($route) > $array['count']) {
            $this->setController($this->route['error_controller']);
            return;
        }

        $this->defaultRoute($this->routes[$array['num']]);

        if(! empty($array['controller']))
            $this->setController($array['controller']);

        if(! empty($array['action']))
            $this->setAction($array['action']);

        $this->setArguments($array['arguments']);
    }

    /**
     * return what route will fit
     *
     * @access protected
     * @param array $route
     * @return int
     */
    protected function getNumRoute($route)
    {
        foreach ($this->routes As $key => $value) {

            $map = StringWorking::map($value['url']);

            if(count($route) != count($map))
                continue;

            $thisroute = true;

            foreach($map as $key_m => $value_m) {
                if(strpos($value_m, '{') !== false)
                    $thisroute = StringWorking::samePatterns($value_m, $route[$key_m]);
                elseif($route[$key_m] != $value_m)
                    $thisroute = false;
            }

            if($thisroute)
                return $key;
        }

        return count($this->routes) - 1;
    }


    /**
     * @param string $url
     * @param array $dirty
     * @param int
     * @return array
     */
    protected function routeValues($url, $dirty, $num)
    {
        $array = ['controller' => null, 'action' => null, 'count' => 0, 'arguments' => [], 'num' => $num];

        $map = explode('/', StringWorking::clearUri($url));

        $array['count'] = count($map);

        foreach ($map as $key => $value) {

            if (isset($dirty[$key]) && strpos($value, '{') !== false && strpos($value, '}') !== false) {

                $name = StringWorking::getName($value);

                if ($name == 'controller' || $name == 'action') {
                    $array[$name] = StringWorking::clearRoute($value, $dirty[$key]);
                } else {
                    $array['arguments'][$name] = StringWorking::clearRoute($value, $dirty[$key]);
                }
            }
        }

        if (strpos($url, '{personal_route}') !== false && ! empty($this->entity)) {
            $try_again = false;

            if (($personal = $this->entity->getRoute($array['arguments']['personal_route']))) {

                $array['controller'] = $personal['controller'];
                $array['action'] = $personal['action'];

                foreach ($this->route['arguments'] as $key => $value) {
                    if (isset($personal['arguments'][$key]) && $personal['arguments'][$key] != $value)
                        $try_again = true;
                }
            } else {
                $try_again = true;
            }

            if ($try_again) {
                array_splice($this->routes, $num, 1);
                $num = $this->getNumRoute($dirty);
                $array = $array = $this->routeValues($this->routes[$num]['url'], $dirty, $num);
            }
        }

        unset($array['arguments']['personal_route']);

        return $array;
    }
}
