<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Logic;

use Wzj\ShortVideoParse\Enumerates\BiliQualityType;
use Wzj\ShortVideoParse\Enumerates\UserGentType;
use Wzj\ShortVideoParse\Exception\ErrorVideoException;
use Wzj\ShortVideoParse\Utils\CommonUtil;

/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/6/10 - 12:50
 **/
class BiliLogic extends Base
{

    private $quality = BiliQualityType::LEVEL_112;
    private $aid;
    private $cid;
    private $contents;

    private $cover;

    private $title;

    /**
     * BiliLogic初始化.
     * @param string $cookie
     * @param int $quality
     */
    public function init(string $cookie, int $quality)
    {
        $this->setSiteSessionCookie($cookie);
        $this->quality = $quality;
    }

    public function setAidAndCid()
    {
        $contents = $this->get($this->url, [], [
            'User-Agent' => UserGentType::WIN_USER_AGENT
        ]);
        preg_match('/"aid":([0-9]+),/i', $contents, $aid);
        preg_match('/"cid":([0-9]+),/i', $contents, $cid);
        if (CommonUtil::checkEmptyMatch($aid) || CommonUtil::checkEmptyMatch($cid)) {
            throw new ErrorVideoException("aid或cid获取不到参数");
        }

        $this->aid = $aid[1];
        $this->cid = $cid[1];
    }

    /**
     * @desc 请在b站网站打开浏览器调试工具获取cookie并找到SESSDATA=xxxx 这个就是登录凭证。b站cookie有效期不短，好像有2个月，建议自己做cookie维护
     * @return string
     */
    public function getSiteSessionCookie(): string
    {
        return parent::getSiteSessionCookie(); // TODO: Change the autogenerated stub
    }

    public function setContents()
    {
        $apiUrl = 'https://api.bilibili.com/x/player/playurl';
        $contents = $this->get($apiUrl, [
            'avid' => $this->aid,
            'cid' => $this->cid,
            'qn' => $this->quality,
            'otype' => 'json',
            'type' => 'mp4',
            'platform' => 'html5',
        ], [
            'Cookie' => $this->getSiteSessionCookie(),
            'Referer' => 'https://m.bilibili.com/video/av84665662',
            'origin' => 'https://m.bilibili.com',
            'Host' => 'api.bilibili.com',
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/7.0.14(0x17000e29) NetType/WIFI Language/zh_CN',
        ]);
        $this->contents = $contents;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getAid()
    {
        return $this->aid;
    }

    /**
     * @return mixed
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    public function getVideoUrl()
    {
        return $this->contents['data']['durl'][0]['url'] ?? '';
    }

    public function getVideoImage()
    {
        return '';
    }

    public function getVideoDesc()
    {
        return '';
    }

    public function getUsername()
    {
        return '';
    }

    public function getUserPic()
    {
        return '';
    }


}