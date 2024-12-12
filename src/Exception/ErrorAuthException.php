<?php

namespace Wzj\ShortVideoParse\Exception;

class ErrorAuthException extends Exception
{

    public function __construct($message = "")
    {
        parent::__construct("ErrorAuth : " . $message, self::ERROR_AUTH_CODE, null);
    }
}