<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse\Tools;

use Wzj\ShortVideoParse\Interfaces\IVideo;

/**
 *
 */
class XiaoHongShu extends Base implements IVideo
{
    public function start(string $url): array
    {
        $this->make();
        $this->logic->setOriginalUrl($url);
        $this->logic->checkUrlHasTrue();
        $this->logic->setItemIds();
        $this->logic->setContents();
        return $this->exportData();
    }
}