<?php
$code = $e->getCode();
if($code == \Pico\PicoTestException::LOGIN_REQUIRED)
{
    require_once dirname(__FILE__)."/login-form.php";
}
if($code == \Pico\PicoTestException::TOKEN_REQUIRED
|| $code == \Pico\PicoTestException::TOKEN_EXPIRE
|| $code == \Pico\PicoTestException::TOKEN_INVALID
)
{
    require_once dirname(__FILE__)."/token-form.php";
}
if($code == \Pico\PicoTestException::TEST_NOT_FOR_YOU)
{
    require_once dirname(__FILE__)."/not-for-you.php";
}