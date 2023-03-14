<?php

namespace WS;

class WSException extends \Exception
{
    private $previous;

    /**
     * @param string $message Exception message
     * @param int $code Exception code
     * @param \Exception $previous Previous exception
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code);
        if (!is_null($previous)) {
            $this->previous = $previous;
        }
    }
}
