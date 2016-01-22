<?php

namespace Sixx\Mail;

/**
 * Sixx\Mail\Mailer
 *
 * @package    Sixx
 * @subpackage Mail
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2015, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
class Mailer
{

    protected $email;

    public function __construct()
    {
        $transport = new \Swift_MailTransport();
        $this->email = new \Swift_Mailer($transport);
    }

    /**
     * @param $to
     * @param $form
     * @param $subject
     * @param $body
     * @param null $failures
     * @return bool
     */
    public function send($to, $form, $subject, $body, &$failures = null)
    {
        $message = new \Swift_Message();

        $message->setSubject($subject)
            ->setFrom($form) //['info@six-x.org' => 'Search engine']
            ->setTo($to) //['sapsan4eg@ya.ru' => 'Yurchik']
            ->setBody($body, 'text/html');

        if (! $this->email->send($message, $failures)) {
            return false;
        }

        return true;
    }

    /**
     * return string from template
     *
     * @param   string
     * @return  string
     */
    public function getTemplate($name)
    {
        $dir = '';
        if (defined('DIR_EMAIL_VIEWS'))
            $dir = DIR_EMAIL_VIEWS;
        elseif (defined('DIR_VIEWS'))
            $dir = DIR_VIEWS;

        $file = $dir . $name . '.' . defined('FILE_VIEW') ? FILE_VIEW : 'tpl';

        if (file_exists($file))
            return file_get_contents($file);

        return '';
    }
}
