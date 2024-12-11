<?php

namespace Wzj\ShortVideoParse\Logic;

use Wzj\ShortVideoParse\Enumerates\UserGentType;
use Wzj\ShortVideoParse\Exception\ErrorVideoException;
use Wzj\ShortVideoParse\Utils\CommonUtil;

/**
 * 努力努力再努力！！！！！
 * Author：wzj、smalls
 * Github：https://github.com/smalls0098
 * Email：wzj177@163.com
 * Date：2020/8/5 - 16:21
 **/
class H5KuaiShouLogic extends Base
{

    private $contents;


    public function setContents()
    {

        $shareId = '';
        $shareToken = '';
        if (strpos($this->url, 'v.kuaishou.com') != false) {
            $redirectUrl = $this->redirects($this->url, [], [
                'User-Agent' => UserGentType::ANDROID_USER_AGENT,
                'Referer' => 'https://m.kuaishou.com/'
            ]);
            preg_match('/photoId=(.*?)\&/', $redirectUrl, $matches);
            preg_match('/shareId=(.*?)\&/', $redirectUrl, $matches2);
            if (isset($matches2[1])) {
                $shareId = $matches2[1];
            }
            preg_match('/shareToken=(.*?)\&/', $redirectUrl, $matches3);
            if (isset($matches3[1])) {
                $shareToken = $matches3[1];
            }
        } else {
            preg_match('/short-video\/(.*?)\?/', $this->url, $matches);
        }

        if (CommonUtil::checkEmptyMatch($matches)) {
            throw new ErrorVideoException("photoId获取不到");
        }

        $did = 'web_5499d94ee54f4d5b83720cc5c571cd45';
        $didV = 1732264985000;
        $contents = $this->request('post', 'https://v.m.chenzhongtech.com/rest/wd/photo/info/', [
            'json' => [
                'efid' => '3x6wwfra7j5z3qc',
                'env' => 'SHARE_VIEWER_ENV_TX_TRICK',
                'photoId' => $matches[1],
                'isLongVideo' => true,
                'h5Domain' => 'v.m.chenzhongtech.com',
                'kpn' => 'KUAISHOU',
                'shareChannel' => 'share_copylink',
                'shareId' => $shareId,
                'shareMethod' => 'TOKEN',
                'shareObjectId' => '5212635237669845630',
                'shareResourceType' => 'PHOTO_OTHER',
                'shareToken' => $shareToken,
                'subBiz' => 'BROWSE_SLIDE_PHOTO'
            ],
            'headers' => [
                'Content-Type' => 'application/json;charset=UTF-8',
                'User-Agent' => UserGentType::ANDROID_USER_AGENT,
                'Cookie' => sprintf("did=%s;didv=%s;", $did, $didV),
                'Referer' => $this->url
            ]
        ]);
        if (!empty($contents) && $contents['result'] === 1) {
            $this->contents = $contents;
        }
//        $result = $this->request()
//        if (!$this->toolsObj->getCookie()) {
//            $cookie = $this->getCookie($this->url, [
//                'User-Agent' => UserGentType::ANDROID_USER_AGENT,
//                'Referer' => $this->url
//            ]);
//            preg_match('/did=(web_.*?);/', $cookie, $matches);
//            if (CommonUtil::checkEmptyMatch($matches)) {
//                throw new ErrorVideoException("did获取不到");
//            }
//            $did = $matches[1];
//            preg_match('/client_key=(.*?);/', $cookie, $matches);
//            if (CommonUtil::checkEmptyMatch($matches)) {
//                throw new ErrorVideoException("client_key获取不到");
//            }
//            $clientKey = $matches[1];
//            preg_match('/clientid=([0-9]);/', $cookie, $matches);
//            $clientId = isset($matches[1]) ? $matches[1] : 3;
//            $cookie   = 'did=' . $did . '; client_key=' . $clientKey . '; clientid=' . $clientId . '; didv=' . time() . '000;';
//        } else {
//            $cookie = $this->toolsObj->getCookie();
//        }
//        $res = $this->get($this->url, [], [
//            'User-Agent' => UserGentType::ANDROID_USER_AGENT,
//            'Cookie' => $cookie
//        ]);
//        preg_match('/window\.pageData= ([\s\S]*?)<\/script>/i', $res, $matches);
//        if (CommonUtil::checkEmptyMatch($matches)) {
//            throw new ErrorVideoException("contents获取不到");
//        }
//        $this->contents = json_decode($matches[1], true);
    }

    /**
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }


    public function getVideoUrl()
    {
        return isset($this->contents['video']['srcNoMark']) ? $this->contents['video']['srcNoMark'] : '';
    }

    public function getVideoImage()
    {
        return isset($this->contents['video']['poster']) ? $this->contents['video']['poster'] : '';
    }

    public function getVideoDesc()
    {
        return isset($this->contents['video']['caption']) ? $this->contents['video']['caption'] : '';
    }

    public function getUsername()
    {
        return isset($this->contents['user']['avatar']) ? $this->contents['user']['avatar'] : '';

    }

    public function getUserPic()
    {
        return isset($this->contents['user']['name']) ? $this->contents['user']['name'] : '';

    }


}