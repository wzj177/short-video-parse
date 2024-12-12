<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Exception;

/**
 * Created By 1
 * Author：wzj、smalls
 * Email：wzj177@163.com
 * Date：2020/4/26 - 22:39
 **/
class ErrorVideoException extends Exception
{

    public function __construct($message = "")
    {
        parent::__construct("ErrorVideo : " . $message, self::ERROR_VIDEO_CODE, null);
    }
}