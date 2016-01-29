<?php

namespace Sixx\Translate;

/**
 * Sixx\Translate\Mui
 *
 * @package    Sixx
 * @subpackage Translate
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class Mui
{
    protected static $default = 'en';
    protected static $lang;
    protected static $dictionary = [];
    protected static $name;
    protected static $listLanguages = [];
    protected static $entity;
    protected static $started = false;

    /**
     * initiation
     *
     * @param	object
     * @param	array
     * @param	array
     * @param	object
     * @param	array
     * @param	array
     */
    public static function start(EntityInterface $entity, \Sixx\Net\Request $request, $default = null)
    {
        require_once(\Sixx\Load\Loader::slash(__DIR__) . 'translate.php');
        self::$entity = $entity;
        self::$default = $default ? $default : self::$default;
        self::$lang = self::$default;
        self::$name = 'no name';
        self::setLanguages(self::$entity->listLanguages());

        if (count(self::listLanguages()) > 1) {
            $array = [
                isset($request->get['my_mui_language']) ? $request->get['my_mui_language'] : false,
                isset($request->post['my_mui_language']) ? $request->post['my_mui_language'] : false,
                isset($request->session->data['language']) ? $request->session->data['language'] : false,
                isset($cookie['language']) ? $cookie['language'] : false
            ];

            $check_browser = true;

            foreach ($array as $value) {
                if ($value != false && array_key_exists($value, self::listLanguages())) {
                    self::$name	= self::listLanguages()[$value]['name'];
                    self::$lang	= $value;
                    $check_browser	= false;
                    break;
                }
            }

            if ($check_browser && ! empty($server['HTTP_ACCEPT_LANGUAGE'])) {
                $browser_languages = explode(',', $server['HTTP_ACCEPT_LANGUAGE']);

                foreach ($browser_languages As $browser_language) {
                    foreach (self::listLanguages() As $key => $value) {
                        $locale = explode(',', $value['locale']);
                        if (in_array($browser_language, $locale))
                            self::$lang = $key;
                    }
                }
            }
        } elseif (count(self::listLanguages()) == 1) {
            self::$name = self::listLanguages()[0]['name'];
            self::$lang = self::listLanguages()[0]['lang'];
        }

        if (! isset($request->session->data['language'])) {
            $request->session->data['language'] = self::$lang;
        } elseif ($request->session->data['language'] != self::$lang) {
            $request->session->data['language'] = self::$lang;
        }

        if ( ! isset($request->cookie['language'])) {
            setcookie('language',self::$lang, time() + 60 * 60 * 24 * 30, '/', $request->server['HTTP_HOST']);
        } elseif ($request->cookie['language'] != self::$lang) {
            setcookie('language', self::$lang, time() + 60 * 60 * 24 * 30, '/', $request->server['HTTP_HOST']);
        }

        self::$started = true;
    }

    /**
     * get translated string
     *
     * @access	public
     * @param	string
     * @return	string
     */
    public static function get($key)
    {
        if(! self::$started)
            throw new \Exception('Class translate didn\'t started. ');

        if (empty(self::$dictionary[$key])) {
            self::$dictionary[$key] = self::translate($key, self::$lang);
            if (self::$dictionary[$key] === $key && self::$lang != self::$default)
                self::$dictionary[$key] = self::translate($key, self::$default);
        }
        return self::$dictionary[$key];
    }

    /**
     * get translated string from sql
     *
     * @access	protected
     * @param	string
     * @param   string
     * @return	string
     */
    private static function translate($key, $locale)
    {
        if (($label = self::$entity->translate($key, $locale)))
            return $label;
        else
            return $key;
    }

    /**
     * get type language
     *
     * @access	public
     * @return	string
     */
    public static function getLang()
    {
        return self::$lang;
    }

    /**
     * get name language
     *
     * @access	public
     * @return	string
     */
    public static function getName()
    {
        return self::$name;
    }

    /**
     * get list language type => name
     *
     * @access	public
     * @return	array
     */
    public static function listLanguages()
    {
        return self::$listLanguages;
    }

    /**
     * get list language type => name
     *
     * @access	public
     * @param	array
     */
    protected static function setLanguages($lang)
    {
        self::$listLanguages = $lang;
    }
}
