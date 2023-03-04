<?php

namespace Pico;

class PicoTestException extends \Exception
{
    const TEST_NOT_FOR_YOU   = 1001;
    const TEST_NOT_IN_PERIOD = 1002;
    const TOKEN_INVALID      = 2001;
    const TOKEN_EXPIRE       = 2002;
    const TOKEN_REQUIRED     = 2003;
    const LOGIN_REQUIRED     = 3001;

    private $previous;
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code);
        if (!is_null($previous)) {
            $this->previous = $previous;
        }
    }
}
