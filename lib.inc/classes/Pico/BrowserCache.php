<?php

namespace Pico;

class BrowserCache
{
    public function __construct()
    {
        //Do nothing
    }
    public function setMaxAge($age)
    {
        $ts = gmdate("D, d M Y H:i:s", time() + $age) . " GMT";
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: max-age=$age");
    }
}
