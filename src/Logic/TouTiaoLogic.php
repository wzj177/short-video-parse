<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Logic;

use Wzj\ShortVideoParse\Enumerates\UserGentType;
use Wzj\ShortVideoParse\Exception\ErrorVideoException;
use Wzj\ShortVideoParse\Utils\CommonUtil;

class TouTiaoLogic extends Base
{
    protected ?string $itemId;

    protected $contents;

    protected string $title = '';

    protected string $redirect_url = '';
    protected string $xg_pc_redirect_url;
    protected string $xg_h5_redirect_url;
    protected string $poster_url = '';

    public function setItemId()
    {
        if (strpos($this->url, 'v.ixigua.com') || strpos($this->url, 'm.toutiao.com')) {
            $this->redirect_url = $this->redirects($this->url);
            if (strpos($this->redirect_url, 'm.toutiao.com/video')) {
                $this->xg_h5_redirect_url = str_replace('m.toutiao.com/video', 'm.ixigua.com/video', $this->redirect_url);
                $this->xg_pc_redirect_url = str_replace('m.toutiao.com/video', 'www.ixigua.com', $this->redirect_url);
            }
        } else if (strpos($this->url, 'toutiao.com/video') || strpos($this->url, 'www.toutiao.com/video')) {
            $this->xg_h5_redirect_url = str_replace('www.', 'm.', $this->url);
            $this->xg_pc_redirect_url = str_replace('m.toutiao.com/video', 'www.ixigua.com', $this->xg_h5_redirect_url);
            $this->redirect_url = '';
        } else if (strpos($this->url, 'www.ixigua.com')) {
            preg_match('/\/(\d+)$/', $this->url, $matches);
            if (!empty($matches[1])) {
                $this->xg_h5_redirect_url = sprintf("https://m.ixigua.com/video/%s", $matches[1]);
                $this->xg_pc_redirect_url = sprintf("https://www.ixigua.com/%s", $matches[1]);
                $this->redirect_url = '';
            }
        }
    }

    public function setContents()
    {
        if (!$this->redirect_url && !$this->xg_pc_redirect_url && !$this->xg_h5_redirect_url) {
            throw new ErrorVideoException("获取不到指定的内容信息");
        }

        try {
            $xgContents = $this->get($this->xg_pc_redirect_url, [
                'wid_try' => 1
            ], [
                'Referer' => 'https://www.ixigua.com/7366982872042832399?id=7336558282368090639',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                'Cookie' => '__ac_nonce=06757ba68005745b959ea; __ac_signature=_02B4Z6wo00f0185n9DwAAIDAnJjUJdfOevfOR.CAAJTXf6'
            ]);


            if ($this->xg_pc_redirect_url && $this->xg_h5_redirect_url && false !== strpos($xgContents, 'window.getSSRHydratedData')) {
                return $this->setXiGuaVideoContents($xgContents);
            }
        } catch (\Throwable $e) {
        }

        try {
            $contents = $this->get($this->redirect_url, [], [
                'Referer' => $this->url,
                'User-Agent' => UserGentType::ANDROID_USER_AGENT
            ]);
            return $this->setTouTiaoVideoContents($contents);
        } catch (\Throwable $e) {
            throw new ErrorVideoException("最终获取不到内容信息");
        }
    }

    protected function setXiGuaVideoContents($contents)
    {
        preg_match('/window\.getSSRHydratedData\s*=\s*function\s*\(\)\s*{[^}]*data\s*=\s*(\{.*\});/', $contents, $matches);
        if (CommonUtil::checkEmptyMatch($matches)) {
            throw new ErrorVideoException("获取不到西瓜视频指定的加密信息");
        }
        $jsonData = str_replace(":undefined", ":\"none\"", $matches[1]);
        $decodedData = json_decode($jsonData, true);
        if (empty($decodedData['anyVideo']['gidInformation']['packerData']['video']['vid'])) {
            throw new ErrorVideoException("获取不到西瓜视频vid");
        }

        $vid = $decodedData['anyVideo']['gidInformation']['packerData']['video']['vid'];
        $this->poster_url = $decodedData['anyVideo']['gidInformation']['packerData']['video']['poster_url'] ?? '';
        $this->title = $decodedData['anyVideo']['gidInformation']['packerData']['video']['title'] ?? '';
        $ygRequestInfo = $this->make365ygVideoUrlAndQueryByVid($vid);
        $contents = $this->get($ygRequestInfo['url'], $ygRequestInfo['query'], [
            'Referer' => $this->url,
            'User-Agent' => UserGentType::ANDROID_USER_AGENT
        ]);

        if (empty($contents)) {
            throw new ErrorVideoException("获取不到西瓜视频地址内容信息");
        }
        $jsonData = json_decode($contents, true);

        if (empty($jsonData) || $jsonData['code'] !== 0 || empty($jsonData['data']['video_list'])) {
            throw new ErrorVideoException("获取西瓜视频地址内容接口请求错误，参数错误");
        }

        $this->contents = $jsonData['data'];
        if (empty($this->poster_url)) {
            $this->poster_url = $contents['data']['poster_url'];
        }
    }

    protected function make365ygVideoUrlAndQueryByVid(string $vid): array
    {
        $url = "https://ib.365yg.com/video/urls/v/1/toutiao/mp4/{$vid}";
        $r = time() * 1000;
        $config = [
            'videoInterfacePath' => '/video/urls/v/1/toutiao/mp4/'
        ];
        $interUrlPath = $config['videoInterfacePath'] . $vid . '?r=' . $r;
        $crc32 = crc32($interUrlPath);
        $unsignedCrc32 = $crc32 & 0xFFFFFFFF;

        return [
            'url' => $url,
            'query' => [
                'r' => $r,
                'nobase64' => true,
                's' => $unsignedCrc32,
                'aid' => 3586,
                'logo_type' => 'unwatermarked',
                'vfrom' => 'xgplayer'
            ]
        ];
    }

    protected function setTouTiaoVideoContents($contents)
    {
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
        } elseif (!empty($this->contents['video_list'])) {
            $videoList = array_values($this->contents['video_list']);
            $videoDefinitionList = array_column($videoList, null,'definition');
            if (!empty($videoDefinitionList['1080p'])) {
                return $videoDefinitionList['1080p']['main_url'];
            }

            if (!empty($videoDefinitionList['720p'])) {
                return $videoDefinitionList['720p']['main_url'];
            }

            if (!empty($videoDefinitionList['540p'])) {
                return $videoDefinitionList['540p']['main_url'];
            }

            if (!empty($videoDefinitionList['360p'])) {
                return $videoDefinitionList['360p']['main_url'];
            }
        }
        return '';
    }

    public function getVideoImage()
    {
        if (!empty($this->contents['Result']['Data']['CoverUrl'])) {
            return $this->contents['Result']['Data']['CoverUrl'];
        }

        return  $this->poster_url;
    }

    public function getImages(): array
    {
        return [];
    }

    public function getVideoDesc(): string
    {
        return $this->title;
    }

    public function getUsername()
    {
        return '';
    }

    public function getUserPic()
    {
        return '';
    }

    public function getHttpClient(array $options = []): \GuzzleHttp\Client
    {
        $options['timeout'] = 1.0;

        return parent::getHttpClient($options);
    }
}