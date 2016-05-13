<?php

namespace Sixx;

use Sixx\DependencyInjection\Inject;

class App
{
    /**
     * App constructor.
     */
    public function __construct()
    {
        require_once "startup.php";
        Inject::bind('Sixx\\Log\\LoggerInterface', ['default' => ['name' => 'Sixx\\Log\\Log', 'single' => true]]);
        Inject::instantiation("\\Sixx\\Web");
    }
}
