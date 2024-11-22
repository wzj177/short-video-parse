<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Tools;

use Wzj\ShortVideoParse\Interfaces\IVideo;

/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/4/26 - 21:57
 **/
class DouYin extends Base implements IVideo
{

    /**
     * 更新时间：2020/7/31
     * @param string $url
     * @return array
     */
    public function start(string $url): array
    {
        $this->make();
        $this->logic->setOriginalUrl($url);
        $this->logic->checkUrlHasTrue();
        $this->logic->setItemIds();
        $this->logic->setContents();
        return $this->exportData();
    }

    protected function exportData(): array
    {
        $data =  parent::exportData();
        $proxyConfig = $this->proxyConfig['douyin'] ?? [];

        $data['images'] = empty($data['video_url']) || false !== strpos($data['video_url'], '.mp3') ? $this->logic->getImages() : [];
        if (!empty($data['video_url']) && !empty($proxyConfig['video'])) {
            $data['proxy_video_download_url'] = $proxyConfig['video'] . '?url=' . urlencode($data['video_url']);
        }

        if (!empty($data['img_url']) && !empty($proxyConfig['image'])) {
            $data['proxy_image_download_url'] = $proxyConfig['image'] . '?url=' . urlencode($data['img_url']);
        }

        return $data;
    }
}