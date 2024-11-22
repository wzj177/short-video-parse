<?php
/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/4/26 - 22:15
 **/

use Wzj\ShortVideoParse\VideoManager;

require __DIR__ . '/../vendor/autoload.php';

$logDir = __DIR__ . '/logs';
!is_dir($logDir) && mkdir($logDir, 0777, true);
// 抖音
try {
    $result = VideoManager::DouYin()->start('https://v.douyin.com/Cei3b1GM/');
    file_put_contents($logDir . '/douyin.json', json_encode($result, JSON_UNESCAPED_UNICODE));
} catch (\Exception $e) {
    echo '抖音解析失败：', $e->getMessage(), PHP_EOL;
}
// B站
try {
    $result = VideoManager::Bili()->setCookie("SESSDATA=aba1384e%2C1747560303%2C09dd5%2Ab2CjDQK89fZzO9xbTjP-iAtY1Lzl5HrTkXEqDovfvXENOniXTbvUfwWme-1LUQaND12jASVjZUY2pVVHc3NHU2ZmRjTThzaV9yWlJVX3c2QTJVRHFKYWJOcmMzclV5SGRvdklONEdDblIwZFVZRUFub0dVVENheW5uaVllV1F6X1E4S2VYNThFdFlRIIEC")->start('https://www.bilibili.com/video/BV1kdUWYYENY/');
    file_put_contents($logDir . '/bilibili.json', json_encode($result, JSON_UNESCAPED_UNICODE));
} catch (\Exception $e) {
    echo 'B站解析失败：', $e->getMessage(), PHP_EOL;
}
// 小红书
try {
    $result = VideoManager::XiaoHongShu()->start('https://xhslink.com/a/QEIIVYilUPJY');
    file_put_contents($logDir . '/xiaohongshu.json', json_encode($result, JSON_UNESCAPED_UNICODE));
} catch (\Exception $e) {
    echo '小红书解析失败：', $e->getMessage(), PHP_EOL;
}
// 头条
try {
    $result = VideoManager::TouTiao()->start('https://m.toutiao.com/is/iDRw4nTv/');
    file_put_contents($logDir . '/toutiao.json', json_encode($result, JSON_UNESCAPED_UNICODE));
} catch (\Exception $e) {
    echo '头条解析失败：', $e->getMessage(), PHP_EOL;
}
// 快手
try {
    $result = VideoManager::KuaiShou()->start('https://v.kuaishou.com/3ReJ7G');
    file_put_contents($logDir . '/kuaishou.json', json_encode($result, JSON_UNESCAPED_UNICODE));
} catch (\Exception $e) {
    echo '快手解析失败：', $e->getMessage(), PHP_EOL;
}
// 微视
try {
    $result = VideoManager::WeiShi()->start('https://video.weishi.qq.com/eyatIQSW');
    file_put_contents($logDir . '/weishi.json', json_encode($result, JSON_UNESCAPED_UNICODE));
} catch (\Exception $e) {
    echo '微视解析失败：', $e->getMessage(), PHP_EOL;
}

// 最右
try {
    $result = VideoManager::ZuiYou()->start('https://share.xiaochuankeji.cn/hybrid/share/post?pid=366079555&zy_to=applink&share_count=1&m=4e7d12220fd8f8c202256add9bf91e14&d=7a22d5bdb0fbe6a3c53817785ed7c2b256bf8cecb79692ce99520e2c977ec144&app=zuiyou&recommend=r0&name=n0&title_type=t0');
    file_put_contents($logDir . '/zuiyou.json', json_encode($result, JSON_UNESCAPED_UNICODE));
} catch (\Exception $e) {
    echo '最右解析失败：', $e->getMessage(), PHP_EOL;
}