<?php

namespace Sixx;

class App
{
    /**
     * App constructor.
     * @param string|null $pathToConfig
     */
    public function __construct($pathToConfig = null)
    {
        try {
             new Application("\\Sixx\\Web", $pathToConfig);
        } catch (\Exception $e) {
            if (headers_sent()) {
                return null;
            }
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            echo 'Bad deeds, really something going wrong.';
            echo $e->getMessage();
        }
    }
}
