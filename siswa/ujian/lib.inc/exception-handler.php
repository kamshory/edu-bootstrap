<?php
$code = $e->getCode();

if($code == \Pico\PicoTestException::LOGIN_REQUIRED)
{
    echo "INCLUDE LOGIN FORM HERE";
}
if($code == \Pico\PicoTestException::TOKEN_REQUIRED
|| $code == \Pico\PicoTestException::TOKEN_EXPIRE
|| $code == \Pico\PicoTestException::TOKEN_INVALID
)
{
    echo "INCLUDE TOKEN FORM HERE";
}