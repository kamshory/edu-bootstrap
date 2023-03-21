<?php
$code = $e->getCode();
if($code == \Pico\PicoTestException::LOGIN_REQUIRED)
{
    require_once __DIR__."/login-form.php";
}
if($code == \Pico\PicoTestException::TOKEN_REQUIRED
|| $code == \Pico\PicoTestException::TOKEN_EXPIRE
|| $code == \Pico\PicoTestException::TOKEN_INVALID
)
{
    require_once __DIR__."/token-form.php";
}
if($code == \Pico\PicoTestException::TEST_NOT_FOR_YOU)
{
    require_once __DIR__."/not-for-you.php";
}