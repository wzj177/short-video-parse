<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Interfaces;
/**
 * Created By 1
 * Author：wzj、smalls
 * Email：wzj177@163.com
 * Date：2020/4/26 - 21:59
 **/
interface IVideo
{

    /**
     * @param string $url
     * @return array
     */
    public function start(string $url): array;

}