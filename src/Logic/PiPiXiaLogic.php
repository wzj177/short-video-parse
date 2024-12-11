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
 * Date：2024/6/10 - 14:13
 **/
class PiPiXiaLogic extends Base
{

    private $itemId;
    private $contents;


    public function setItemId()
    {
    }

    public function setContents()
    {
        preg_match("/mid=(\d+)&pid=(\d+)/i", $this->url, $match);
        if (empty($match) || !isset($match[1]) || !isset($match[2])) {
            throw new ErrorVideoException("获取不到mid 和  pid信息");
        }

        $params = [
            'mid' => (int)$match[1],
            'pid' => (int)$match[2],
            'type' => 'post',
        ];

        $contents = $this->request('post', 'https://h5.pipigx.com/ppapi/share/fetch_content', [
            'json' => $params,
            'headers' => [
                'Content-Type' => 'application/json;charset=UTF-8',
                'User-Agent' => UserGentType::ANDROID_USER_AGENT,
            ]
        ]);
        if (empty($contents['data']['post']['videos'])) {
            throw new ErrorVideoException("获取不到指定的内容信息");
        }

        $videos = array_values($contents['data']['post']['videos']);
        $cover = '';
        if (!empty($contents['data']['post']['imgs'][0])) {
            $imgId = $contents['data']['post']['imgs'][0]['id'];
            $cover = "https://file.ippzone.com/img/view/id/{$imgId}";
        }

        $this->contents = [
            'video' => $videos[0],
            'cover' => $cover,
        ];
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
    public function getItemId()
    {
        return $this->itemId;
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
        return !empty($this->contents['video']['url']) ? $this->contents['video']['url'] : ($this->contents['video']['urlwm'] ?? '');
    }


    public function getVideoImage()
    {
        return $this->contents['cover'] ?? '';
    }

    public function getVideoDesc()
    {
        return $this->contents['data']['item']['share']['title'] ?? '';
    }

    public function getUserPic()
    {
        return $this->contents['data']['item']['author']['avatar']['url_list'][0]['url'] ?? '';
    }

    public function getUsername()
    {
        return $this->contents['data']['item']['author']['name'] ?? '';
    }
}