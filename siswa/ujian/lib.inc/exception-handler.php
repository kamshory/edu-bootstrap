<?php
$code = $e->getCode();

if($code == \Pico\PicoTestException::LOGIN_REQUIRED)
{
    //echo "INCLUDE LOGIN FORM HERE";
    require_once dirname(__FILE__)."/login-form.php";
}
if($code == \Pico\PicoTestException::TOKEN_REQUIRED
|| $code == \Pico\PicoTestException::TOKEN_EXPIRE
|| $code == \Pico\PicoTestException::TOKEN_INVALID
)
{
    require_once dirname(__FILE__)."/token-form.php";
}