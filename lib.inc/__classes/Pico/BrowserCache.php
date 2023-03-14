<?php

namespace Pico;

class BrowserCache
{
    /**
     * Set max age of cache
     *
     * @param int $age Cache lifetime in second
     * @return void
     */
    public static function setMaxAge($age)
    {
        $ts = gmdate("D, d M Y H:i:s", time() + $age) . " GMT";
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: max-age=$age");
    }
}
