<?php

namespace Sixx\Net;

/**
 * Sixx\Net\Response
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
class Response
{

    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2012-02-13).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    protected $status = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    /**
     * default header
     * @var array
     */
    protected $headers = [
        'status' => 200,
        'Content-Type' => 'text/html; charset=UTF-8',
    ];

    protected $content = '';

    protected $protocol = 'HTTP/1.1';

    /**
     * Response to request
     */
    public function response()
    {
        $this->headers();
        echo $this->content;
    }

    /**
     * Set headers
     */
    protected function headers()
    {
        if (headers_sent()) {
            return;
        }

        $headers = $this->prepare($this->headers);
        header($this->protocol . ' ' . $headers['status'] . ' ' . $this->status[$headers['status']], true, $headers['status']);

        foreach ($headers as $name => $value) {
            if ($name == 'status') {
                continue;
            }
            header($name.': '.$value, false, $headers['status']);
        }
    }

    /**
     * @param array|null $headers
     * @return array
     */
    protected function prepare(array $headers = null)
    {
        if (empty($headers['status'])) {
            $headers['status'] = 200;
        }

        $headers['status'] = $this->getStatus($headers['status'])['code'];

        if (empty($headers['Date'])) {
            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            $headers['Date'] = $date->format('D, d M Y H:i:s').' GMT';
        }

        if (empty($headers['Content-Type'])) {
            $headers['Content-Type'] = 'text/html; charset=UTF-8';
        }

        if (!empty($headers['Transfer-Encoding']) && !empty($headers['Content-Length'])) {
            unset($headers['Content-Length']);
        }

        return $headers;
    }

    /**
     * @param string $content
     */
    public function setContent($content = '')
    {
        if (!empty($content)) {
            $this->content = $content;
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param array $header
     */
    public function setHeaders(array $header)
    {
        if (is_array($header)) {
            foreach ($header as $name => $value) {
                $this->headers[$name] = $value;
            }
        }
    }

    /**
     * @param int|null $code
     * @return int
     */
    public function getStatus($code = null)
    {
        $status = !empty($code) ? (int)$code : 200;
        $status = array_key_exists($status, $this->status) ? $status : 200;

        return ['code' => $status, 'description' => $this->status[$status]];
    }

    /**
     * @param $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
}
