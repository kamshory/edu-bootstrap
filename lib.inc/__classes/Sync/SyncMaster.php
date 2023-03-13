<?php
namespace Sync;
class SyncMaster {
    public function buildURL($url, $httpQuery, $keepOriginal = true)
    {
        $original = array();
        if($keepOriginal)
        {
            $parsed = parse_url($url);
            if(isset($parsed['query']))
            {
                parse_str($parsed['query'], $original);
            }
        }
        $combined = array_merge($original, $httpQuery);
        
        if(stripos($url, "?") !== false)
        {
            $arr = explode("?", $url);
            $url = $arr[0];
        }        
        $url = $url."?".http_build_query($combined);
        return $url;
    }
}