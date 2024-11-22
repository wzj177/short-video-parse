<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Logic;

use Wzj\ShortVideoParse\Enumerates\UserGentType;
use Wzj\ShortVideoParse\Exception\ErrorVideoException;
use Wzj\ShortVideoParse\Utils\CommonUtil;

/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/6/10 - 13:05
 **/
class DouYinLogic extends Base
{

    private $redirectUrl;

    private $contents;
    private $itemId;

    public function setItemIds()
    {
        if (strpos($this->url, '/share/video')) {
            $url = $this->url;
        } else {
            $url = $this->redirects($this->url, [], [
                'User-Agent' => UserGentType::ANDROID_USER_AGENT,
                'Referer' => 'https://www.douyin.com/'
            ]);
        }

        $this->redirectUrl = $url;
        preg_match('/[video|note]\/([0-9]+)\//i', $url, $matches);
        if (CommonUtil::checkEmptyMatch($matches)) {
            throw new ErrorVideoException("item_id获取不到");
        }

        $this->itemId = $matches[1];
    }

    public function setContents()
    {
        $contents = $this->get($this->redirectUrl, [], [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1',
            'Referer' => $this->url,
        ]);
        $startIndex = strpos($contents, 'async="">_ROUTER_DATA = ') + strlen('async="">_ROUTER_DATA = ');
        $endIndex = strpos($contents, '_ROUTER_DATA.s');
        $jsonStr = rtrim(substr($contents, $startIndex, $endIndex - $startIndex), "\n");
        $jsonStr = rtrim($jsonStr, ";");
        $json = json_decode($jsonStr, true);
        if (empty($json) || !isset($json["loaderData"])) {
            throw new \Smalls\VideoTools\Exception\ErrorVideoException("无法获取视频信息");
        }
        if (strpos($this->redirectUrl, 'note') !== false) {
            $videoItemList = $json["loaderData"]["note_(id)/page"]["videoInfoRes"]['item_list'] ?? [];
        } else {
            $videoItemList = $json["loaderData"]["video_(id)/page"]["videoInfoRes"]['item_list'] ?? [];
        }

        if (empty($videoItemList[0])) {
            throw new ErrorVideoException("不存在item_list无法获取视频信息");
        }

        $this->contents = $videoItemList[0];
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
        if (strpos($this->redirectUrl, 'note') !== false) {
            if (isset($this->contents['video']['play_addr']['uri'])) {
                return $this->contents['video']['play_addr']['uri'];
            }
        }

        if (isset($this->contents['video']['play_addr']['url_list'])) {
            $url =  str_replace('playwm', 'play', $this->contents['video']['play_addr']['url_list'][0]);
            $contents = $this->request('get', $url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1',
                    'Referer' => 'https://www.douyin.com/',
                ],
                'allow_redirects' => false,
            ]);
            $pattern = '/<a\s+[^>]*href=["\']([^"\']+)["\']/i';
            preg_match_all($pattern, $contents, $matches);
            if (!empty($matches[1][0])) {
                return $matches[1][0];
            }
            return $url;
        }
        return '';
    }

    public function getVideoImage()
    {
        if (isset($this->contents['video']['cover']['url_list'])) {
            return $this->contents['video']['cover']['url_list'][0];
        }

        return '';
    }

    public function getImages(): array
    {
        $images = [];
        if (isset($this->contents['images'])) {
            foreach ($this->contents['images'] as $image) {
                if (!isset($image['url_list'][0])) {
                    continue;
                }
                $images[] = $image['url_list'][0];
            }
        }

        return $images;
    }


    public function getVideoDesc()
    {
        return $this->contents['desc'] ?? '';
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
