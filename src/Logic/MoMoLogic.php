<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Logic;

use Wzj\ShortVideoParse\Enumerates\UserGentType;
use Wzj\ShortVideoParse\Exception\ErrorVideoException;
use Wzj\ShortVideoParse\Utils\CommonUtil;

/**
 * Created By 1
 * Author：wzj、smalls
 * Email：wzj177@163.com
 * Date：2024/6/10 - 18:22
 **/
class MoMoLogic extends Base
{

    private $contents;
    private $feedId;


    public function setFeedId()
    {
        preg_match("/feedid=(.*?)&/i", $this->url, $match);
        if (!empty($match) && isset($match[1])) {
            $this->feedId = $match[1];
        } else {
            throw new ErrorVideoException("陌陌获取不到feed_id信息");
        }
    }

    public function setContents()
    {
        $contents = $this->post('https://m.immomo.com/inc/microvideo/share/profiles', [
            'feedids' => $this->feedId
        ], [
            'User-Agent' => UserGentType::ANDROID_USER_AGENT,
        ]);

        if (isset($contents['ec']) && $contents['ec'] != 200) {
            throw new ErrorVideoException("获取不到指定的内容信息");
        }

        $this->contents = $contents;
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
        return $this->contents['data']['list'][0]['video']['video_url'] ?? '';
    }


    public function getVideoImage()
    {
        return $this->contents['data']['list'][0]['video']['cover']['l'] ?? '';
    }

    public function getVideoDesc()
    {
        return $this->contents['data']['list'][0]['video']['decorator_texts'] ?? '';
    }

    public function getUserPic()
    {
        return $this->contents['data']['list'][0]['user']['img'] ?? '';
    }

    public function getUsername()
    {
        return $this->contents['data']['list'][0]['user']['name'] ?? '';
    }
    public function getImages(): array
    {
        return [];
    }

}