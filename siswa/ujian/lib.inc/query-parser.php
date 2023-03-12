<?php
if(isset($_GET) && !empty($_GET))
{
    if(isset($_GET['arg1']))
    {
        $arg1 = trim($_GET['arg1']);
    }
    if(isset($_GET['arg2']))
    {
        $arg2 = trim($_GET['arg2']);
    }
}

if(stripos($_SERVER['REQUEST_URI'], '?') !== false)
{
    list($path, $query) = explode('?', $_SERVER['REQUEST_URI'], 2);
    $gets = array();
    parse_str($query, $gets); //NOSONAR
    foreach($gets as $k=>$v)
    {
        $_GET[$k] = $v;
    }
}
