<?php

namespace Wzj\ShortVideoParse\Logic;


use Wzj\ShortVideoParse\Enumerates\UserGentType;
use Wzj\ShortVideoParse\Exception\ErrorVideoException;
use Wzj\ShortVideoParse\Utils\CommonUtil;

class XiaoHongShuLogic extends Base
{
    private $redictUrl;
    private $contents;
    private $itemId;

    public function setItemIds()
    {
        if (strpos($this->url, 'xiaohongshu.com/')) {
            $url = $this->url;
        } else {
            $url = $this->redirects($this->url, [], [
                'User-Agent' => UserGentType::ANDROID_USER_AGENT,
                'Referer' => 'https://xiaohongshu.com/'
            ]);
        }

        $this->redictUrl = $url;
        preg_match('/item\/([0-9a-zA-Z]+)?/i', $url, $matches);
        if (CommonUtil::checkEmptyMatch($matches)) {
            throw new ErrorVideoException("item_id获取不到");
        }
        $this->itemId = $matches[1];
    }

    public function setContents()
    {
        $contents = $this->request('get', $this->redictUrl, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', // user-agent请求中必须，否则返回状态码444。常规UA无有效数据返回，可能存在某种校验，临时使用postmanUA头，保证正常返回
                'Referer' => 'https://www.xiaohongshu.com/',
            ],
            'allow_redirects' => false,
        ]);
        $startIndex = strpos($contents, '>window.__INITIAL_STATE__=') + strlen('>window.__INITIAL_STATE__=');
        $endIndex = strpos($contents, '</script><script>window.__SETUP_SERVER_STATE__=');
        $jsonStr = rtrim(substr($contents, $startIndex, $endIndex - $startIndex), "\n");
        $json = json_decode($jsonStr, true);
        if (empty($json) || empty($json["noteData"]["data"]["noteData"])) {
            throw new ErrorVideoException("无法获取视频信息");
        }

        $this->contents = $json["noteData"]["data"]["noteData"];
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
        if (!empty($this->contents['video']['media']['stream'])) {
            $streams = $this->contents['video']['media']['stream'];
            if (!empty($streams['h264'])) {
                return $streams['h264'][0]['masterUrl'] ?? '';
            }
        }

        return '';
    }

    public function getVideoImage()
    {
        if (!empty($this->contents['video']['image'])) {
            $image = $this->contents['video']['image'];
            if (!empty($image['firstFrameFileid'])) {
                return "https://sns-img-hw.xhscdn.com/{$image['firstFrameFileid']}";
            }
            if (!empty($image['thumbnailFileid'])) {
                return "https://sns-img-hw.xhscdn.com/{$image['thumbnailFileid']}";
            }
        } else {
            $images = $this->getImages();
            return $images[0] ?? '';
        }

        return '';
    }

    public function getImages(): array
    {
        $images = [];
        if (empty($this->contents['video']) && !empty($this->contents['imageList'])) {
            foreach ($this->contents['imageList'] as $image) {
                $images[] = $image['url'];
            }
        }


        return $images;
    }

    public function getVideoDesc(): string
    {
        $str = '';
        if (!empty($this->contents['title'])) {
            $str .= $this->contents['title'];
        }

        if (!empty($this->contents['desc'])) {
            $str .= "\n" . $this->contents['desc'];
        }

        return $str;
    }

    public function getUsername()
    {
        return $this->contents['author']['nickname'] ?? '';
    }

    public function getUserPic()
    {
        if (isset($this->contents['author']['avatar_thumb']['url_list'])) {
            return $this->contents['author']['avatar_thumb']['url_list'][0];
        }
        return '';
    }
}