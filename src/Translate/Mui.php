<?php

namespace Sixx\Translate;

use Sixx\Net\Request;

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
 *
 */
class Mui
{
    protected $default = 'en';
    protected $lang;
    protected $dictionary = [];
    protected $name;
    protected $listLanguages = [];

    /**
     * @var EntityInterface
     */
    protected $entity;

    /**
     * @param EntityInterface $entity
     * @param \Sixx\Net\Request $request
     * @param null $default
     */
    public function __construct(EntityInterface $entity, Request $request, $default = null)
    {
        $this->entity = $entity;
        $this->default = $default ? $default : $this->default;
        $this->lang = $this->default;
        $this->name = 'no name';
        $this->setLanguages($this->entity->listLanguages());

        if (count($this->listLanguages()) > 1) {
            $array = [
                isset($request->get['my_mui_language']) ? $request->get['my_mui_language'] : false,
                isset($request->post['my_mui_language']) ? $request->post['my_mui_language'] : false,
                isset($request->session['my_mui_language']) ? $request->session['my_mui_language'] : false,
                isset($cookie['my_mui_language']) ? $cookie['my_mui_language'] : false
            ];

            $checkBrowser = true;

            foreach ($array as $value) {
                if ($value != false && array_key_exists($value, $this->listLanguages())) {
                    $this->name	= $this->listLanguages()[$value]['name'];
                    $this->lang	= $value;
                    $checkBrowser = false;
                    break;
                }
            }

            if ($checkBrowser && !empty($request->server['HTTP_ACCEPT_LANGUAGE'])) {
                $browserLanguages = explode(',', $request->server['HTTP_ACCEPT_LANGUAGE']);

                foreach ($browserLanguages as $browser_language) {
                    foreach ($this->listLanguages() as $key => $value) {
                        $locale = explode(',', $value['locale']);
                        if (in_array($browser_language, $locale)) {
                            $this->lang = $key;
                        }
                    }
                }
            }
        } elseif (count($this->listLanguages()) == 1) {
            $this->name = $this->listLanguages()[0]['name'];
            $this->lang = $this->listLanguages()[0]['lang'];
        }

        if (!isset($request->session['my_mui_language']) || $request->session['my_mui_language'] != $this->lang) {
            $request->session['my_mui_language'] = $this->lang;
        }

        if (!isset($request->cookie['my_mui_language']) || $request->cookie['my_mui_language'] != $this->lang) {
            setcookie('language',$this->lang, time() + 60 * 60 * 24 * 30, '/', $request->server['HTTP_HOST']);
        }
    }

    /**
     * get translated string
     *
     * @access public
     * @param string
     * @return string
     * @throws \Exception
     */
    public function get($key)
    {
        if (empty($this->dictionary[$key])) {
            $this->dictionary[$key] = $this->translate($key, $this->lang);
            if ($this->dictionary[$key] === $key && $this->lang != $this->default) {
                $this->dictionary[$key] = $this->translate($key, $this->default);
            }
        }

        return $this->dictionary[$key];
    }

    /**
     * get translated string from sql
     *
     * @access	protected
     * @param	string
     * @param   string
     * @return	string
     */
    protected function translate($key, $locale)
    {
        if (($label = $this->entity->translate($key, $locale))) {
            return $label;
        }

        return $key;
    }

    /**
     * get type language
     *
     * @access	public
     * @return	string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * get name language
     *
     * @access	public
     * @return	string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * get list language type => name
     *
     * @access	public
     * @return	array
     */
    public function listLanguages()
    {
        return $this->listLanguages;
    }

    /**
     * get list language type => name
     *
     * @access	public
     * @param	array
     */
    protected function setLanguages($lang)
    {
        $this->listLanguages = $lang;
    }
}
