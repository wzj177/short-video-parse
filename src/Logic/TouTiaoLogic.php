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
 * Date：2020/6/10 - 14:00
 **/
class TouTiaoLogic extends Base
{

    protected $itemId;
    private $contents;
    private $title;

    private $redirect_url;

    public function setItemId()
    {
        if (strpos($this->url, 'v.ixigua.com') || strpos($this->url, 'm.toutiao.com')) {
            $this->redirect_url = $this->redirects($this->url);
        }
    }

    public function setContents()
    {
        if (!$this->redirect_url) {
            throw new ErrorVideoException("获取不到指定的内容信息");
        }
        $contents = $this->get($this->redirect_url, ['i' => $this->itemId], [
            'Referer' => $this->url,
            'User-Agent' => UserGentType::ANDROID_USER_AGENT
        ]);
        $pattern = '/<meta[^>]*property="og:title"[^>]*content="([^"]*)"/';
        preg_match($pattern, $contents, $match);
        if (CommonUtil::checkEmptyMatch($match)) {
            throw new ErrorVideoException("获取不到指定的标题信息");
        }

        $this->title = $match[1];
        $pattern = '/<script id="RENDER_DATA" type="application\/json">(.+?)<\/script>/s';
        preg_match($pattern, $contents, $match);
        if (CommonUtil::checkEmptyMatch($match)) {
            throw new ErrorVideoException("获取不到加密信息");
        }
        $info = json_decode(urldecode($match[1]), true);
        if (empty($info)) {
            throw new ErrorVideoException("获取不到指定的解密信息");
        }

        if (empty($info['articleInfo']['playAuthTokenV2'])) {
            throw new ErrorVideoException("获取不到xplayer 播放 token信息");
        }

        $decodePlayAuthTokenV2 = json_decode(base64_decode($info['articleInfo']['playAuthTokenV2']), true);
        if (empty($decodePlayAuthTokenV2["GetPlayInfoToken"])) {
            throw new ErrorVideoException("解析播放token 失败");
        }

        $tokenQueryParams = explode('&', $decodePlayAuthTokenV2["GetPlayInfoToken"]);
        $queryParams = [];
        foreach ($tokenQueryParams as $queryParam) {
            list($key, $value) = explode('=', $queryParam);
            if ($key === "X-Amz-SignedQueries" || $key === "X-Amz-Credential") {
                $value = urldecode($value);
            }
            $queryParams[$key] = $value;
        }
        $contents = $this->get("https://vod.bytedanceapi.com", $queryParams, [
            'User-Agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"
        ]);
        $this->contents = $contents;
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

    public function getVideoUrl(): string
    {
        if (!empty($this->contents['Result']['Data']['PlayInfoList'])) {
            $playList = $this->contents['Result']['Data']['PlayInfoList'];
            if (!empty($playList[3]['MainPlayUrl'])) {
                return $playList[3]['MainPlayUrl'];
            }
            if (!empty($playList[2]['MainPlayUrl'])) {
                return $playList[2]['MainPlayUrl'];
            }
            if (!empty($playList[1]['MainPlayUrl'])) {
                return $playList[1]['MainPlayUrl'];
            }
            if (!empty($playList[0]['MainPlayUrl'])) {
                return $playList[0]['MainPlayUrl'];
            }
        }

        return '';
    }

    public function getVideoImage()
    {
        return $this->contents['Result']['Data']['CoverUrl'] ?? '';
    }

    public function getVideoDesc()
    {
        return $this->title;
    }

    public function getUsername()
    {
        return $this->contents['data']['media_user']['screen_name'] ?? '';
    }

    public function getUserPic()
    {
        return $this->contents['data']['media_user']['avatar_url'] ?? '';
    }

}