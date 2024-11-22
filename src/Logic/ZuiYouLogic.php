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
 * Date：2020/6/10 - 14:13
 **/
class ZuiYouLogic extends Base
{

    private $pid;
    private $contents;
    private $id;


    public function setPid()
    {
    }
    public function setContents()
    {
        $contents = file_get_contents($this->url);
        $title = '';
        preg_match('/<meta[^>]+name=["\']?description["\']?[^>]+content=["\']?([^"\'>]+)["\']?/', $contents, $tMatches);
        if (isset($tMatches[1])) {
            $title = $tMatches[1];
        }
        $startIndex = strpos($contents, 'APP_INITIAL_STATE') + strlen('APP_INITIAL_STATE') + 1;
        $endIndex = strpos($contents, '}}</script><script id="__LOADABLE_REQUIRED_CHUNKS__" type="application/json">');
        $jsonStr = substr($contents, $startIndex, $endIndex - $startIndex + 2);
        $json = json_decode($jsonStr, true);
        $post = $json['sharePost']['postDetail']['post'] ?? [];
        if (!empty($post['content'])) {
            $title = $post['content'];
        }
        $cover = "";
        if (!empty($post['imgs'][0]['urls'])) {
            $img = $post['imgs'][0]['urls']['540Webp'] ?? ($post['imgs'][0]['urls']['360'] ?? []);
            if (!empty($img) && !empty($img['urls'])) {
                $cover = $img['urls'][0];
            }
        }
        $url = "";
        if (!empty($post['videos'])) {
            $videos = array_values($post['videos']);
            $url = $videos[0]['url'];
        }

        $this->contents = [
            'url' => $url,
            'title' => $title,
            'cover' => $cover
        ];
    }

    public function parseId()
    {
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
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
        return $this->contents['url'];
    }


    public function getVideoImage()
    {
        return $this->contents['cover'];
    }

    public function getImages(): array
    {
        return [];
    }

    public function getVideoDesc()
    {
        return $this->contents['title'];
    }

    public function getUsername()
    {
        return $this->contents['data']['post']['member']['name'] ?? '';
    }

    public function getUserPic()
    {
        return $this->contents['data']['post']['member']['avatar_urls']['aspect_low']['urls'][0] ?? '';
    }

}