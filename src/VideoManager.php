<?php
declare (strict_types=1);

namespace Wzj\ShortVideoParse;

use Wzj\ShortVideoParse\Exception\InvalidManagerException;
use Wzj\ShortVideoParse\Interfaces\IVideo;
use Wzj\ShortVideoParse\Tools\Bili;
use Wzj\ShortVideoParse\Tools\DouYin;
use Wzj\ShortVideoParse\Tools\HuoShan;
use Wzj\ShortVideoParse\Tools\KuaiShou;
use Wzj\ShortVideoParse\Tools\LiVideo;
use Wzj\ShortVideoParse\Tools\MeiPai;
use Wzj\ShortVideoParse\Tools\MiaoPai;
use Wzj\ShortVideoParse\Tools\MoMo;
use Wzj\ShortVideoParse\Tools\PiPiGaoXiao;
use Wzj\ShortVideoParse\Tools\PiPiXia;
use Wzj\ShortVideoParse\Tools\QQVideo;
use Wzj\ShortVideoParse\Tools\QuanMingGaoXiao;
use Wzj\ShortVideoParse\Tools\ShuaBao;
use Wzj\ShortVideoParse\Tools\TaoBao;
use Wzj\ShortVideoParse\Tools\TouTiao;
use Wzj\ShortVideoParse\Tools\WeiBo;
use Wzj\ShortVideoParse\Tools\WeiShi;
use Wzj\ShortVideoParse\Tools\XiaoHongShu;
use Wzj\ShortVideoParse\Tools\XiaoKaXiu;
use Wzj\ShortVideoParse\Tools\XiGua;
use Wzj\ShortVideoParse\Tools\ZuiYou;

/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/4/26 - 21:51
 **/

/**
 * @method static HuoShan HuoShan(...$params)
 * @method static DouYin DouYin(...$params)
 * @method static KuaiShou KuaiShou(...$params)
 * @method static TouTiao TouTiao(...$params)
 * @method static XiGua XiGua(...$params)
 * @method static WeiShi WeiShi(...$params)
 * @method static PiPiXia PiPiXia(...$params)
 * @method static ZuiYou ZuiYou(...$params)
 * @method static MeiPai MeiPai(...$params)
 * @method static LiVideo LiVideo(...$params)
 * @method static QuanMingGaoXiao QuanMingGaoXiao(...$params)
 * @method static PiPiGaoXiao PiPiGaoXiao(...$params)
 * @method static MoMo MoMo(...$params)
 * @method static ShuaBao ShuaBao(...$params)
 * @method static XiaoKaXiu XiaoKaXiu(...$params)
 * @method static Bili Bili(...$params)
 * @method static WeiBo WeiBo(...$params)
 * @method static MiaoPai MiaoPai(...$params)
 * @method static QQVideo QQVideo(...$params)
 * @method static TaoBao TaoBao(...$params)
 * @method static XiaoHongShu XiaoHongShu(...$params)
 */
class VideoManager
{

    public function __construct()
    {
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        $app = new self();
        return $app->create($method, $params);
    }

    /**
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws InvalidManagerException
     */
    private function create(string $method, array $params)
    {
        $className = __NAMESPACE__ . '\\Tools\\' . $method;
        if (!class_exists($className)) {
            throw new InvalidManagerException("the method name does not exist . method : {$method}");
        }
        return $this->make($className, $params);
    }

    /**
     * @param string $className
     * @param array $params
     * @return mixed
     * @throws InvalidManagerException
     */
    private function make(string $className, array $params)
    {
        $app = new $className($params);
        if ($app instanceof IVideo) {
            return $app;
        }
        throw new InvalidManagerException("this method does not integrate IVideo . namespace : {$className}");
    }
}