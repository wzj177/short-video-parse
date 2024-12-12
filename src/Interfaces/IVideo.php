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

    /**
     * 二维码登录-生成登录链接
     * @param array $params
     * @return string
     */
    public function makeQrcodeLoginUrl(array $params = []);

    /**
     * 二维码登录-登录处理
     * @param array $params
     * @return void
     */
    public function qrcodeLogin(array $params = []);


    /**
     * 普通账号密码登录
     * @param array $params
     * @return mixed
     */
    public function login(array $params);

}