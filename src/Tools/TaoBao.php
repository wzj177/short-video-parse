<?php

namespace Wzj\ShortVideoParse\Tools;

use Wzj\ShortVideoParse\Interfaces\IVideo;

/**
 * 努力努力再努力！！！！！
 * Author：wzj、smalls
 * Github：https://github.com/smalls0098
 * Email：wzj177@163.com
 * Date：2020/8/13 - 22:51
 **/
class TaoBao extends Base implements IVideo
{


    public function start(string $url): array
    {
        $this->make();
        $this->logic->setOriginalUrl($url);
        $this->logic->checkUrlHasTrue();
        $this->logic->setContents();
        return $this->exportData();
    }


}