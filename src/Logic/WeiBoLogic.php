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
 * Date：2020/6/13 - 21:05
 **/
class WeiBoLogic extends Base
{

    private $statusId;
    private $contents;

    public function setStatusId()
    {
        preg_match('/(\d+:\d+)/i', $this->url, $matches);
        if (!empty($matches[1])) {
            $this->fid = $matches[1];
        } else {
            preg_match('/(\d+\/\S+)/i', $this->url, $matches);
            if (!empty($matches[1])) {
                $this->fid = $matches[1];
            }
        }
        if (empty($this->fid)) {
            throw new ErrorVideoException("获取不到fid参数信息");
        }
    }

    public function setContents()
    {
        $fid = $this->getfid();
        if (empty($fid)) {
            throw new ErrorVideoException("获取不到fid参数信息");
        }
        //
        if (strpos($fid, ':') !== false) {
            return $this->setContentsByH5WeiBo($fid);
        }

        return $this->setContentsByPcWeiBo($fid);
    }

    private function setContentsByPcWeiBo($fid)
    {
        $idArr = explode('/', $fid);
        $url = "https://weibo.com/ajax/statuses/show";
        $headers = [
            "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"
        ];
        $response = $this->getHttpClient($this->getBaseOptions())->post("https://passport.weibo.com/visitor/genvisitor2", [
            "headers" => $headers,
            'multipart' => [
                [
                    'name' => 'cb',
                    'contents' => 'visitor_gray_callback',
                ],
            ]
        ]);
        $beforeContent = $response->getBody()->getContents();
        $jsonStr = str_replace([
            "window.visitor_gray_callback && visitor_gray_callback(",
            ");"
        ], '', $beforeContent);
        $json = json_decode($jsonStr, true);
        if (empty($json) || $json["retcode"] !== 20000000) {
            throw new ErrorVideoException("获取不到指定的内容信息");
        }

        $nextContents = $this->get($url, [
            'id' => $idArr[1],
            'locale' => 'zh-CN'
        ], [
            'Cookie' => implode(';', [
                'SUB=' . $json['data']['sub'],
                'SUBP=' . $json['data']['subp']
            ]),
            'User-Agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36",
        ]);
        $this->contents = $nextContents;
    }

    private function setContentsByH5WeiBo($fid)
    {
        $url = "https://h5.video.weibo.com/api/component?page=/show/{$fid}";
        $contents = $this->post($url, [
            'data' => '{"Component_Play_Playinfo":{"oid":"' . $fid . '"}}'
        ], [
            'Referer' => $this->url,
            'User-Agent' => UserGentType::ANDROID_USER_AGENT,
        ]);
        if (empty($contents) || $contents['code'] != 100000) {
            throw new ErrorVideoException("获取不到视频信息");
        }

        $this->contents = $contents['data']['Component_Play_Playinfo'] ?? [];
    }

    private function getContents()
    {
        return $this->contents;
    }

    /**
     * @return mixed
     */
    public function getFid()
    {
        return $this->fid;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getVideoUrl()
    {
        if (strpos($this->fid, ':') !== false) {
            $playInfo = $this->contents;
            if (!empty($playInfo['urls'])) {
                if (isset($playInfo['urls']['高清 1080P'])) {
                    return false === strpos($playInfo['urls']['高清 1080P'], 'http') ? 'https:' . $playInfo['urls']['高清 1080P'] : $playInfo['urls']['高清 1080P'];
                }

                if (isset($playInfo['urls']['高清 720P'])) {
                    return false === strpos($playInfo['urls']['高清 720P'], 'http') ? 'https:' . $playInfo['urls']['高清 720P'] : $playInfo['urls']['高清 720P'];
                }

                if (isset($playInfo['urls']['标清 480P'])) {
                    return false === strpos($playInfo['urls']['标清 480P'], 'http') ? 'https:' . $playInfo['urls']['标清 480P'] : $playInfo['urls']['标清 480P'];
                }

                if (isset($playInfo['urls']['流畅 360P'])) {
                    return false === strpos($playInfo['urls']['流畅 360P'], 'http') ? 'https:' . $playInfo['urls']['流畅 360P'] : $playInfo['urls']['流畅 360P'];
                }
            }
        } else if (strpos($this->fid, '/') !== false) {
            if (!empty($this->contents['page_info']['media_info'])) {
                if (!empty($this->contents['page_info']['media_info']['stream_url_hd'])) {
                    return $this->contents['page_info']['media_info']['stream_url_hd'];
                }

                if (!empty($this->contents['page_info']['media_info']['stream_url'])) {
                    return $this->contents['page_info']['media_info']['stream_url'];
                }
            }
        }

        return '';
    }

    public function getVideoImage()
    {
        if (strpos($this->fid, ':') !== false) {
            $playInfo = $this->contents;
            if (isset($playInfo['cover_image'])) {
                return false === strpos($playInfo['cover_image'], 'http') ? 'https:' . $playInfo['cover_image'] : $playInfo['cover_image'];
            }
        } else if (strpos($this->fid, '/') !== false) {
            return $this->contents['page_info']['page_pic'] ?? '';
        }

        return '';
    }

    public function getImages(): array
    {
        $images = [];
        if (strpos($this->fid, ':') !== false) {
            return [];
        } else if (strpos($this->fid, '/') !== false) {
            if (empty($this->contents['page_info']['media_info']) && !empty($this->contents['pic_infos'])) {
                $picInfos = array_values($this->contents['pic_infos']);
                foreach ($picInfos as $picInfo) {
                    if (!empty($picInfo['original'])) {
                        $images[] = $picInfo['original']['url'];
                    } else if (!empty($picInfo['large'])) {
                        $images[] = $picInfo['large']['url'];
                    } else if (!empty($picInfo['bmiddle'])) {
                        $images[] = $picInfo['bmiddle']['url'];
                    } else if (!empty($picInfo['thumbnail'])) {
                        $images[] = $picInfo['thumbnail']['url'];
                    }
                }
            }
        }

        return $images;
    }

    public function getVideoDesc()
    {
        if (strpos($this->fid, ':') !== false) {
            $playInfo = $this->contents;
            if (!empty($playInfo['text'])) {
                return str_replace("  ", "", strip_tags($playInfo['text']));
            }
        } else if (strpos($this->fid, '/') !== false) {
            return $this->contents['text_raw'] ?? '';
        }


        return '';
    }

    public function getUsername()
    {
        return $this->contents['status']['user']['screen_name'] ?? '';
    }

    public function getUserPic()
    {
        return $this->contents['status']['user']['profile_image_url'] ?? '';
    }

}