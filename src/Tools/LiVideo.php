<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Tools;
/**
 * Created By 1
 * Author：wzj、smalls
 * Email：wzj177@163.com
 * Date：2020/4/26 - 21:57
 **/

use Wzj\ShortVideoParse\Interfaces\IVideo;

class LiVideo extends Base implements IVideo
{

    /**
     * 更新时间：2020/10/25
     * @param string $url
     * @return array
     */
    public function start(string $url): array
    {
        $this->make();
        $this->logic->setOriginalUrl($url);
        $this->logic->checkUrlHasTrue();
        $this->logic->setVideoId();
        $this->logic->setContents();
        return $this->exportData();
    }
}