<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Tools;

use Wzj\ShortVideoParse\Enumerates\BiliQualityType;
use Wzj\ShortVideoParse\Interfaces\IVideo;

/**
 * B站解析
 * @link 参考：https://lxb007981.github.io/bilibili-API-collect/video/videostream_url.html#%E8%8E%B7%E5%8F%96%E8%A7%86%E9%A2%91%E6%B5%81url-web%E7%AB%AF
 */
class Bili extends Base implements IVideo
{

    private $url = '';

    /**
     * @var string
     */
    private $cookie = '';
    private $quality = BiliQualityType::LEVEL_2;

    /**
     * 更新时间：2020/7/31
     * 暂时还没修复完整
     * @param string $url
     * @return array
     */
    public function start(string $url): array
    {
        $this->url     = $url;
        return $this->execution();
    }

    /**
     * 更新时间：2020/6/10
     * @return array
     */
    public function execution(): array
    {
        $this->make();
        $this->logic->setOriginalUrl($this->url);
        $this->logic->init($this->getCookie(), $this->getQuality());
        $this->logic->checkUrlHasTrue();
        $this->logic->setAidAndCid();
        $this->logic->setContents();
        return $this->exportData();
    }

    /**
     * 设置cookie
     * @param string $cookie
     * @return $this
     */
    public function setCookie(string $cookie = ''): self
    {
        $this->cookie = $cookie;
        return $this;
    }

    /**
     * @return string
     */
    public function getCookie(): string
    {
        return $this->cookie;
    }

    /**
     * 清晰度
     * @param mixed $quality
     * @return Bili
     */
    public function setQuality(int $quality = BiliQualityType::LEVEL_5): Bili
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * 设置URL
     * @param mixed $url
     * @return Bili
     */
    public function setUrl(string $url): Bili
    {
        $this->url = $url;
        return $this;
    }

    protected function exportData(): array
    {
        $data =  parent::exportData(); // TODO: Change the autogenerated stub
        $data['images'] = empty($data['video_url']) ? $this->logic->getImages() : [];

        return $data;
    }

}