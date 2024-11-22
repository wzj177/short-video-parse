<h1>ShortVideoParse</h1>

[//]: # (<p>)

[//]: # (<a href="https://packagist.org/packages/wzj177/short-video-parse"><img src="https://poser.pugx.org/wzj177/short-video-parse/v/stable" alt="Latest Stable Version"></a>)

[//]: # (<a href="https://packagist.org/packages/wzj177/short-video-parse"><img src="https://poser.pugx.org/wzj177/short-video-parse/downloads" alt="Total Downloads"></a>)

[//]: # (<a href="https://packagist.org/packages/wzj177/short-video-parse"><img src="https://poser.pugx.org/wzj177/short-video-parse/v/unstable" alt="Latest Unstable Version"></a>)

[//]: # (<a href="https://packagist.org/packages/wzj177/short-video-parse"><img src="https://poser.pugx.org/wzj177/short-video-parse/license" alt="License"></a>)

[//]: # (</p>)

## 短视频去水印
 本项目是在 [VideoTools](https://github.com/smalls0098/VideoTools) 的基础上修改的，修改目的在于，之前的包里面有些接口失效，同时也对部分接口做了升级，项目部分平台不仅支持视频、封面同时支持图片集采集。

 原先大佬集成了：抖音、B站、火山、头条、快手、梨视频、美拍、陌陌、皮皮搞笑、皮皮虾、全民搞笑、刷宝、微视、小咖秀、最右、微博、秒拍、淘宝等等。
 现在我这边调整测试稳定支持的平台有：抖音、B站、小红书、头条、最右、微博。其他平台可能不支持，有问题可以issues交流。
## 集成案例
<img src="./docs/wechat-mini.jpg" alt="森友去水印解析下载工具箱"/>
## 安装

安装方法一：（需要下载composer.phar到根目录，设置PHP为全局变量）
~~~
php composerphar require wzj177/short-video-parse
~~~
安装方法二：
~~~
composer require wzj177/short-video-parse
~~~

如果需要更新扩展包使用
~~~
composer update wzj177/short-video-parse
~~~
 ********

> 运行环境要求PHP70+
VideoManager使用文档：(可以参考tests/testphp)
 ==
    抖音：VideoManager::DouYin()->start($url); 
>   B站：VideoManager::Bili()->start($url);
>   小红书：VideoManager::XiHongShu()->start($url);
    快手：VideoManager::KuaiShou()->start($url); // 快手原有解析最近也不能用了，暂不支持
>   微博：VideoManager::WeiBo()->start($url);
    火山：VideoManager::HuoShan()->start($url);
    头条：VideoManager::TouTiao()->start($url);
    西瓜：VideoManager::XiGua()->start($url);
    微视：VideoManager::WeiShi()->start($url);
    皮皮虾：VideoManager::PiPiXia()->start($url);
    最右：VideoManager::ZuiYou()->start($url);
   自定义URL配置文件：url-validator
   --
   ````
    例如抖音：$res = VideoManager::KuaiShou([
              'proxy_whitelist' => ['kuaishou'],//白名单，需要提交类名，全部小写
              'proxy' => '$ip:$port',
              'url_validator' => [
                    这边参考config/url-validator.php
              ]
          ])->start($url);
    可以参考config/url-validator.php的格式用参数传递，如果不指定则使用默认的
    不会怎么编写全部使用默认也是可以的
   ````
## 常见问题
### 下载或者播放出现403
有很多平台的视频不能访问，需要使用代理，因此VideoManager新增了代理配置，在配置文件`config/proxy.php`中配置，可以参考`config/proxy.php.example`的格式。代理配置是按照平台配置，但基本下面的方案是通用的，因此可以写成同样的代理地址。以下是我整理的一些解决方案：
1. nginx代理下载
```config
  location /download {
        if ($query_string ~* "^url=(.*)$") {
            set $target_url $1;
        }
        if ($target_url = "") {
            return 400 "Missing URL parameter.";
        }
        # 代理请求到目标 URL
        proxy_pass $target_url;
        # 设置请求头
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        #proxy_set_header Referer "https://m.toutiao.com/";
        proxy_set_header Accept-Encoding "";

        # 支持断点续传
        proxy_set_header Range $http_range;
        proxy_cache_bypass $http_range;

        # 启用错误拦截
        proxy_intercept_errors on;
    }
```
使用方法：`https://video-parse.top/download?url=https://v.kuaishou.com/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
2. 基于php的curl实现
使用curl代理视频和图片真实地址，可以满足播放和下载。同时如果上面的nginx代理下载仍然出现403错误，可以尝试使用curl代理。下面给出视频代理的例子，图片代理类似。
```php
<?php
$url = $_GET["url"] ?? "";
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo "url parameter needs to be set.";
    exit();
}

// 动态设置 Referer
$refer = 'https://baidu.com';
if (false !== strpos($url, 'toutiao')) {
    $refer = 'https://m.toutiao.com/';
} else if (false !== strpos($url, 'weibo')) {
    $refer = 'https://weibo.com/';
} else if (false !== strpos($url, 'douyinvod.com')) {
    $refer = 'https://www.douyin.com/';
}

// 1. 获取文件大小，改用 GET 请求获取响应头
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_NOBODY, false); // 不设置 HEAD 请求
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // 保留头信息
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
curl_setopt($ch, CURLOPT_REFERER, $refer);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 避免长时间等待

$response = curl_exec($ch);
$info = curl_getinfo($ch);
$httpCode = $info['http_code'] ?? 0;

// 检查 HTTP 状态码
if ($httpCode !== 200) {
    echo "Failed to fetch video: HTTP $httpCode.";
    curl_close($ch);
    exit();
}

// 提取文件大小
$filesize = $info['download_content_length'] ?? 0;
if ($filesize <= 0) {
    echo "Failed to retrieve file size.";
    curl_close($ch);
    exit();
}

curl_close($ch);

// 2. 设置头信息
header('Content-Type: video/mp4');
$filename = basename(parse_url($url, PHP_URL_PATH)) ?: md5($url);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . $filesize);
header('Accept-Ranges: bytes');

// 3. 下载文件内容
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
curl_setopt($ch, CURLOPT_REFERER, $refer);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // 直接输出给客户端

curl_exec($ch);
if (curl_errno($ch)) {
    echo "CURL Error: " . curl_error($ch);
}

curl_close($ch);


```
### B站获取1080以上分辨率的视频
 由于B站采用DASH技术后，直接获取1080以上的mp4类型视频已经不行了，也就是说目前VideoManager设置cookie后调用接口最高拿到的是720P。如果需要1080以上，我们的方案是：
1. 在`BiliLogic.php`里面找到`setContents()`方法，将接口地址换成:`https://api.bilibili.com/x/player/wbi/playurl`
2. 请求示例：
`https://api.bilibili.com/x/player/wbi/playurl?bvid=BV1yE1uYDEPh&cid=26494174859&qn=127&fnver=0&fnval=4048&fourk=1&tabId=1180772181`
需携带cookie: SESSDATA=aba1384e%2C1747560303%2C09dd5%2Ab2CjDQK89fZzO9xbTjP-iAtY1Lzl5HrTkXEqDovfvXENOniXTbvUfwWme-1LUQaND12jASVjZUY2pVVHc3xxxxx
3. 响应示例：
```json
{
	"code": 0,
	"message": "0",
	"ttl": 1,
	"data": {
		"from": "local",
		"result": "suee",
		"message": "",
		"quality": 112,
		"format": "hdflv2",
		"timelength": 356379,
		"accept_format": "hdflv2,flv,flv720,flv480,flv360",
		"accept_description": [
			"高清 1080P+",
			"高清 1080P",
			"高清 720P",
			"清晰 480P",
			"流畅 360P"
		],
		"accept_quality": [
			112,
			80,
			64,
			32,
			16
		],
		"video_codecid": 7,
		"seek_param": "start",
		"seek_type": "offset",
		"dash": {
			"duration": 357,
			"minBufferTime": 1.5,
			"min_buffer_time": 1.5,
			"video": [
				{
					"id": 112,
					"baseUrl": "https://xy112x30x92x2xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100143.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=118831&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=085462&traceid=trhFuRHptJrGDP_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=a0a30653358fa7f61ebb526d7df974ca",
					"base_url": "https://xy112x30x92x2xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100143.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=118831&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=085462&traceid=trhFuRHptJrGDP_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=a0a30653358fa7f61ebb526d7df974ca",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100143.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=a0a30653358fa7f61ebb526d7df974ca&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=118831&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100143.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=1df48205d1cc0b8a09744e7da7157d28&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=118831&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100143.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=a0a30653358fa7f61ebb526d7df974ca&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=118831&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100143.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=1df48205d1cc0b8a09744e7da7157d28&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=118831&logo=40000000"
					],
					"bandwidth": 949977,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "hev1.1.6.L120.90",
					"width": 1440,
					"height": 1080,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1122",
						"indexRange": "1123-2018"
					},
					"segment_base": {
						"initialization": "0-1122",
						"index_range": "1123-2018"
					},
					"codecid": 12
				},
				{
					"id": 112,
					"baseUrl": "https://xy125x47x35x34xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-30112.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=242623&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=8b03d2&traceid=trjsNkNipPLdAk_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=9f19f2ea9eeafe4dbdd0ea43ec71de57",
					"base_url": "https://xy125x47x35x34xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-30112.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=242623&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=8b03d2&traceid=trjsNkNipPLdAk_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=9f19f2ea9eeafe4dbdd0ea43ec71de57",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-30112.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=9f19f2ea9eeafe4dbdd0ea43ec71de57&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=242623&logo=A0020000",
						"https://upos-sz-estgoss.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-30112.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=upos&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=5689a671c3922b77e1c8d4486b264ecc&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=242623&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-30112.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=9f19f2ea9eeafe4dbdd0ea43ec71de57&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=242623&logo=A0020000",
						"https://upos-sz-estgoss.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-30112.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=upos&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=5689a671c3922b77e1c8d4486b264ecc&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=242623&logo=40000000"
					],
					"bandwidth": 1939674,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "avc1.640033",
					"width": 1440,
					"height": 1080,
					"frameRate": "30.000",
					"frame_rate": "30.000",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-939",
						"indexRange": "940-1835"
					},
					"segment_base": {
						"initialization": "0-939",
						"index_range": "940-1835"
					},
					"codecid": 7
				},
				{
					"id": 112,
					"baseUrl": "https://xy36x163x204x32xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100027.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=117139&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=ce4330&traceid=trYCMEUWqAlqMq_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=1660c319e9560318d707ebf96906d12e",
					"base_url": "https://xy36x163x204x32xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100027.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=117139&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=ce4330&traceid=trYCMEUWqAlqMq_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=1660c319e9560318d707ebf96906d12e",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100027.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=1660c319e9560318d707ebf96906d12e&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=117139&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100027.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=36427b9ff285ef5799c78d0a6a0bdfa0&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=117139&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100027.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=1660c319e9560318d707ebf96906d12e&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=117139&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100027.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=36427b9ff285ef5799c78d0a6a0bdfa0&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=117139&logo=40000000"
					],
					"bandwidth": 936451,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "av01.0.00M.10.0.110.01.01.01.0",
					"width": 1440,
					"height": 1080,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1021",
						"indexRange": "1022-1917"
					},
					"segment_base": {
						"initialization": "0-1021",
						"index_range": "1022-1917"
					},
					"codecid": 13
				},
				{
					"id": 80,
					"baseUrl": "https://xy118x182x248x56xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100113.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=70409&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=fc5f84&traceid=trgmOCiIpbTvTK_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=6deec88ad60719f36e0eba1aa6c74b59",
					"base_url": "https://xy118x182x248x56xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100113.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=70409&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=fc5f84&traceid=trgmOCiIpbTvTK_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=6deec88ad60719f36e0eba1aa6c74b59",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100113.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=6deec88ad60719f36e0eba1aa6c74b59&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=70409&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100113.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=860ca3d4f0dc877a592f8bc042959e67&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=70409&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100113.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=6deec88ad60719f36e0eba1aa6c74b59&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=70409&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100113.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=860ca3d4f0dc877a592f8bc042959e67&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=70409&logo=40000000"
					],
					"bandwidth": 562862,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "hev1.1.6.L120.90",
					"width": 1440,
					"height": 1080,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1122",
						"indexRange": "1123-2018"
					},
					"segment_base": {
						"initialization": "0-1122",
						"index_range": "1123-2018"
					},
					"codecid": 12
				},
				{
					"id": 80,
					"baseUrl": "https://xy123x129x197x222xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-100050.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=108605&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=42fca9&traceid=trzhhJEIEKbDeP_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=a97f516b3a80e8dfa7b05ae37390d2d9",
					"base_url": "https://xy123x129x197x222xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-100050.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=108605&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=42fca9&traceid=trzhhJEIEKbDeP_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=a97f516b3a80e8dfa7b05ae37390d2d9",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-100050.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=a97f516b3a80e8dfa7b05ae37390d2d9&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=108605&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-100050.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=f4e65634a7dc861ae2adc71502a324a9&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=108605&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-100050.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=a97f516b3a80e8dfa7b05ae37390d2d9&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=108605&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-100050.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=f4e65634a7dc861ae2adc71502a324a9&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=108605&logo=40000000"
					],
					"bandwidth": 868226,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "avc1.640032",
					"width": 1440,
					"height": 1080,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-956",
						"indexRange": "957-1852"
					},
					"segment_base": {
						"initialization": "0-956",
						"index_range": "957-1852"
					},
					"codecid": 7
				},
				{
					"id": 80,
					"baseUrl": "https://xy183x255x115x14xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100026.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=72635&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=273fa1&traceid=traclfKhyEPkgC_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=701bf9dc63639268c463ee9d2a667d5c",
					"base_url": "https://xy183x255x115x14xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100026.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=72635&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=273fa1&traceid=traclfKhyEPkgC_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=701bf9dc63639268c463ee9d2a667d5c",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100026.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=701bf9dc63639268c463ee9d2a667d5c&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=72635&logo=A0020000",
						"https://upos-sz-estgoss.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100026.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=upos&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=c25fbae976880a61292a05404b2b50a7&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=72635&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100026.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=701bf9dc63639268c463ee9d2a667d5c&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=72635&logo=A0020000",
						"https://upos-sz-estgoss.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100026.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=upos&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=c25fbae976880a61292a05404b2b50a7&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=72635&logo=40000000"
					],
					"bandwidth": 580657,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "av01.0.00M.10.0.110.01.01.01.0",
					"width": 1440,
					"height": 1080,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1021",
						"indexRange": "1022-1917"
					},
					"segment_base": {
						"initialization": "0-1021",
						"index_range": "1022-1917"
					},
					"codecid": 13
				},
				{
					"id": 64,
					"baseUrl": "https://xy111x21x244x137xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100111.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=36891&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=8ff57e&traceid=trwJIldfdzMHIj_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=8408c4f4db3c7d809cb20c163a232853",
					"base_url": "https://xy111x21x244x137xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100111.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=36891&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=8ff57e&traceid=trwJIldfdzMHIj_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=8408c4f4db3c7d809cb20c163a232853",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100111.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=8408c4f4db3c7d809cb20c163a232853&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=36891&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100111.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=4d0a43bada0db8aa8984e3b6735a98c0&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=36891&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100111.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=8408c4f4db3c7d809cb20c163a232853&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=36891&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100111.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=4d0a43bada0db8aa8984e3b6735a98c0&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=36891&logo=40000000"
					],
					"bandwidth": 294894,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "hev1.1.6.L120.90",
					"width": 960,
					"height": 720,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1122",
						"indexRange": "1123-2018"
					},
					"segment_base": {
						"initialization": "0-1122",
						"index_range": "1123-2018"
					},
					"codecid": 12
				},
				{
					"id": 64,
					"baseUrl": "https://xy124x225x75x211xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-100048.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=63958&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=1bd666&traceid=trgiccVXSQVRzU_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=0a42307fca44196eb04fcae5e6ecdb41",
					"base_url": "https://xy124x225x75x211xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-100048.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=63958&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=1bd666&traceid=trgiccVXSQVRzU_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=0a42307fca44196eb04fcae5e6ecdb41",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-100048.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=0a42307fca44196eb04fcae5e6ecdb41&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=63958&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-100048.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=3ce4283d348e28a6b6fb83799091af7d&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=63958&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-100048.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=0a42307fca44196eb04fcae5e6ecdb41&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=63958&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-100048.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=3ce4283d348e28a6b6fb83799091af7d&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=63958&logo=40000000"
					],
					"bandwidth": 511288,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "avc1.640028",
					"width": 960,
					"height": 720,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-954",
						"indexRange": "955-1850"
					},
					"segment_base": {
						"initialization": "0-954",
						"index_range": "955-1850"
					},
					"codecid": 7
				},
				{
					"id": 64,
					"baseUrl": "https://xy183x255x115x10xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100024.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=36718&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=1812d3&traceid=trzGfEFoPphOEY_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=1c21baa802abb296a1a4e88a1ecba623",
					"base_url": "https://xy183x255x115x10xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100024.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=36718&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=1812d3&traceid=trzGfEFoPphOEY_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=1c21baa802abb296a1a4e88a1ecba623",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100024.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=1c21baa802abb296a1a4e88a1ecba623&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=36718&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100024.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=15860cfb9a65626b5750b9f6ecff713a&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=36718&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100024.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=1c21baa802abb296a1a4e88a1ecba623&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=36718&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100024.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=15860cfb9a65626b5750b9f6ecff713a&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=36718&logo=40000000"
					],
					"bandwidth": 293511,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "av01.0.00M.10.0.110.01.01.01.0",
					"width": 960,
					"height": 720,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1021",
						"indexRange": "1022-1917"
					},
					"segment_base": {
						"initialization": "0-1021",
						"index_range": "1022-1917"
					},
					"codecid": 13
				},
				{
					"id": 32,
					"baseUrl": "https://xy211x141x224x122xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100110.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=23606&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=38647a&traceid=trnXVyuoXSkiEE_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=bf009d2965eb30ec3b21f99389b70e50",
					"base_url": "https://xy211x141x224x122xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100110.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=23606&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=38647a&traceid=trnXVyuoXSkiEE_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=bf009d2965eb30ec3b21f99389b70e50",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100110.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=bf009d2965eb30ec3b21f99389b70e50&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=23606&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100110.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=19a8a66b31ddda4d9d710f8e507e587e&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=23606&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100110.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=bf009d2965eb30ec3b21f99389b70e50&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=23606&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100110.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=19a8a66b31ddda4d9d710f8e507e587e&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=23606&logo=40000000"
					],
					"bandwidth": 188680,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "hev1.1.6.L120.90",
					"width": 640,
					"height": 480,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1122",
						"indexRange": "1123-2018"
					},
					"segment_base": {
						"initialization": "0-1122",
						"index_range": "1123-2018"
					},
					"codecid": 12
				},
				{
					"id": 32,
					"baseUrl": "https://xy42x228x38x52xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-100047.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=33917&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=27ae14&traceid=trANqYIYvGlmMm_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=1082b0b28ac3aec84c436abc855720cd",
					"base_url": "https://xy42x228x38x52xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-100047.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=33917&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=27ae14&traceid=trANqYIYvGlmMm_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=1082b0b28ac3aec84c436abc855720cd",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-100047.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=1082b0b28ac3aec84c436abc855720cd&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=33917&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-100047.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=a5a032020cb4166a2622fdad2a9d3184&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=33917&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-100047.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=1082b0b28ac3aec84c436abc855720cd&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=33917&logo=A0020000",
						"https://upos-sz-mirrorcoso1.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-100047.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=coso1bv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=a5a032020cb4166a2622fdad2a9d3184&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=33917&logo=40000000"
					],
					"bandwidth": 271118,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "avc1.64001F",
					"width": 640,
					"height": 480,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-954",
						"indexRange": "955-1850"
					},
					"segment_base": {
						"initialization": "0-954",
						"index_range": "955-1850"
					},
					"codecid": 7
				},
				{
					"id": 32,
					"baseUrl": "https://xy125x41x240x7xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100023.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=22732&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=6c689c&traceid=trGnOBtOqWEGdN_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=a942d742e13ab3cf3734b316e1ad99cb",
					"base_url": "https://xy125x41x240x7xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100023.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=22732&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=6c689c&traceid=trGnOBtOqWEGdN_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=a942d742e13ab3cf3734b316e1ad99cb",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100023.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=a942d742e13ab3cf3734b316e1ad99cb&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=22732&logo=A0020000",
						"https://upos-sz-mirrorbd.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100023.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=bdbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=040869ed06c2568736164077651f4372&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=22732&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100023.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=a942d742e13ab3cf3734b316e1ad99cb&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=22732&logo=A0020000",
						"https://upos-sz-mirrorbd.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100023.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=bdbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=040869ed06c2568736164077651f4372&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=22732&logo=40000000"
					],
					"bandwidth": 181700,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "av01.0.00M.10.0.110.01.01.01.0",
					"width": 640,
					"height": 480,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1021",
						"indexRange": "1022-1917"
					},
					"segment_base": {
						"initialization": "0-1021",
						"index_range": "1022-1917"
					},
					"codecid": 13
				},
				{
					"id": 16,
					"baseUrl": "https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100109.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=236ab81a4a9b7ca05f74613dbfcabc94&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17288&logo=A0020000",
					"base_url": "https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100109.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=236ab81a4a9b7ca05f74613dbfcabc94&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17288&logo=A0020000",
					"backupUrl": [
						"https://upos-sz-mirrorbd.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100109.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=bdbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=4efae8111e6914786bc64e1b4e926543&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17288&logo=40000000",
						"https://upos-sz-mirrorbd.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100109.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=bdbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=4efae8111e6914786bc64e1b4e926543&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=2,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17288&logo=40000000"
					],
					"backup_url": [
						"https://upos-sz-mirrorbd.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100109.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=bdbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=4efae8111e6914786bc64e1b4e926543&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17288&logo=40000000",
						"https://upos-sz-mirrorbd.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100109.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=bdbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=4efae8111e6914786bc64e1b4e926543&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=2,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17288&logo=40000000"
					],
					"bandwidth": 138170,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "hev1.1.6.L120.90",
					"width": 480,
					"height": 360,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1121",
						"indexRange": "1122-2017"
					},
					"segment_base": {
						"initialization": "0-1121",
						"index_range": "1122-2017"
					},
					"codecid": 12
				},
				{
					"id": 16,
					"baseUrl": "https://xy36x138x220x230xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-100046.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=20940&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=5c6f4c&traceid=trqrFyOxbugQqE_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=cd639680b0c36dd2b9baf1cb1fc926a8",
					"base_url": "https://xy36x138x220x230xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-100046.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=20940&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=5c6f4c&traceid=trqrFyOxbugQqE_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=cd639680b0c36dd2b9baf1cb1fc926a8",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-100046.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=cd639680b0c36dd2b9baf1cb1fc926a8&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=20940&logo=A0020000",
						"https://upos-sz-mirrorali.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-100046.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=alibv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=b7cf6d8f867fc2e13ad811f18df37a94&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=20940&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-100046.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=cd639680b0c36dd2b9baf1cb1fc926a8&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=20940&logo=A0020000",
						"https://upos-sz-mirrorali.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-100046.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=alibv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=cos&upsig=b7cf6d8f867fc2e13ad811f18df37a94&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=20940&logo=40000000"
					],
					"bandwidth": 167368,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "avc1.64001E",
					"width": 480,
					"height": 360,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-963",
						"indexRange": "964-1859"
					},
					"segment_base": {
						"initialization": "0-963",
						"index_range": "964-1859"
					},
					"codecid": 7
				},
				{
					"id": 16,
					"baseUrl": "https://xy112x46x139x42xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100022.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=17017&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=338083&traceid=trbQIwWQCxwdyi_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=78b157f7c5b217bc2229e9b1d6dab99c",
					"base_url": "https://xy112x46x139x42xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100022.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=17017&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=338083&traceid=trbQIwWQCxwdyi_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=78b157f7c5b217bc2229e9b1d6dab99c",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100022.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=78b157f7c5b217bc2229e9b1d6dab99c&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17017&logo=A0020000",
						"https://upos-sz-mirrorbd.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100022.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=bdbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=371f2754c772db2e4b0aae6e759beaf6&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17017&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859_x1-1-100022.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=78b157f7c5b217bc2229e9b1d6dab99c&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17017&logo=A0020000",
						"https://upos-sz-mirrorbd.bilivideo.com/upgcxcode/59/48/26494174859/26494174859_x1-1-100022.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=bdbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=371f2754c772db2e4b0aae6e759beaf6&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=17017&logo=40000000"
					],
					"bandwidth": 136007,
					"mimeType": "video/mp4",
					"mime_type": "video/mp4",
					"codecs": "av01.0.00M.10.0.110.01.01.01.0",
					"width": 480,
					"height": 360,
					"frameRate": "29.997",
					"frame_rate": "29.997",
					"sar": "1:1",
					"startWithSap": 1,
					"start_with_sap": 1,
					"SegmentBase": {
						"Initialization": "0-1021",
						"indexRange": "1022-1917"
					},
					"segment_base": {
						"initialization": "0-1021",
						"index_range": "1022-1917"
					},
					"codecid": 13
				}
			],
			"audio": [
				{
					"id": 30280,
					"baseUrl": "https://xy115x231x135x145xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-30280.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=15003&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=56b2e2&traceid=trLlAQtUJKuBrW_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=458b1bc64d4aa3491a77ac542454dde4",
					"base_url": "https://xy115x231x135x145xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-30280.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=15003&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=56b2e2&traceid=trLlAQtUJKuBrW_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=458b1bc64d4aa3491a77ac542454dde4",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-30280.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=458b1bc64d4aa3491a77ac542454dde4&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=15003&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-30280.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=515a69becc3e6dfac164f06e9adaa472&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=15003&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-30280.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=458b1bc64d4aa3491a77ac542454dde4&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=15003&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-30280.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=515a69becc3e6dfac164f06e9adaa472&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=15003&logo=40000000"
					],
					"bandwidth": 119885,
					"mimeType": "audio/mp4",
					"mime_type": "audio/mp4",
					"codecs": "mp4a.40.2",
					"width": 0,
					"height": 0,
					"frameRate": "",
					"frame_rate": "",
					"sar": "",
					"startWithSap": 0,
					"start_with_sap": 0,
					"SegmentBase": {
						"Initialization": "0-817",
						"indexRange": "818-1713"
					},
					"segment_base": {
						"initialization": "0-817",
						"index_range": "818-1713"
					},
					"codecid": 0
				},
				{
					"id": 30216,
					"baseUrl": "https://xy49x73x160x66xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-30216.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=5668&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=a76491&traceid=trKHBvzOCWeoGL_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=45215051a33d62cb55aeb26defe8d273",
					"base_url": "https://xy49x73x160x66xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-30216.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=5668&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=a76491&traceid=trKHBvzOCWeoGL_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=45215051a33d62cb55aeb26defe8d273",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-30216.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=45215051a33d62cb55aeb26defe8d273&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=5668&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-30216.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=d1a12ae3869fb73091c8bfc870857271&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=5668&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-30216.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=45215051a33d62cb55aeb26defe8d273&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=5668&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-30216.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=d1a12ae3869fb73091c8bfc870857271&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=5668&logo=40000000"
					],
					"bandwidth": 45263,
					"mimeType": "audio/mp4",
					"mime_type": "audio/mp4",
					"codecs": "mp4a.40.5",
					"width": 0,
					"height": 0,
					"frameRate": "",
					"frame_rate": "",
					"sar": "",
					"startWithSap": 0,
					"start_with_sap": 0,
					"SegmentBase": {
						"Initialization": "0-827",
						"indexRange": "828-1723"
					},
					"segment_base": {
						"initialization": "0-827",
						"index_range": "828-1723"
					},
					"codecid": 0
				},
				{
					"id": 30232,
					"baseUrl": "https://xy118x182x231x138xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-30232.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=11959&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=2406b5&traceid=trsNlhuGkltKTO_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=c2b9a0969049d450607a7fcbd83d3d3b",
					"base_url": "https://xy118x182x231x138xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-30232.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=11959&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=2406b5&traceid=trsNlhuGkltKTO_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=c2b9a0969049d450607a7fcbd83d3d3b",
					"backupUrl": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-30232.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=c2b9a0969049d450607a7fcbd83d3d3b&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=11959&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-30232.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=60f049a0d62ff7b457ab23bdfdd41153&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=11959&logo=40000000"
					],
					"backup_url": [
						"https://xy123x138x84x144xy.mcdn.bilivideo.cn:4483/upgcxcode/59/48/26494174859/26494174859-1-30232.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=mcdn&oi=1857594792&trid=000044e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=c2b9a0969049d450607a7fcbd83d3d3b&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&mcdnid=50012580&bvc=vod&nettype=0&orderid=0,3&buvid=&build=0&f=u_0_0&agrr=1&bw=11959&logo=A0020000",
						"https://upos-sz-mirror08h.bilivideo.com/upgcxcode/59/48/26494174859/26494174859-1-30232.m4s?e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M=&uipk=5&nbs=1&deadline=1732266877&gen=playurlv2&os=08hbv&oi=1857594792&trid=44e8027efc6344648057b84910fbaafeu&mid=482104881&platform=pc&og=hw&upsig=60f049a0d62ff7b457ab23bdfdd41153&uparams=e,uipk,nbs,deadline,gen,os,oi,trid,mid,platform,og&bvc=vod&nettype=0&orderid=1,3&buvid=&build=0&f=u_0_0&agrr=1&bw=11959&logo=40000000"
					],
					"bandwidth": 95556,
					"mimeType": "audio/mp4",
					"mime_type": "audio/mp4",
					"codecs": "mp4a.40.2",
					"width": 0,
					"height": 0,
					"frameRate": "",
					"frame_rate": "",
					"sar": "",
					"startWithSap": 0,
					"start_with_sap": 0,
					"SegmentBase": {
						"Initialization": "0-817",
						"indexRange": "818-1713"
					},
					"segment_base": {
						"initialization": "0-817",
						"index_range": "818-1713"
					},
					"codecid": 0
				}
			],
			"dolby": {
				"type": 0,
				"audio": null
			},
			"flac": null
		},
		"support_formats": [
			{
				"quality": 112,
				"format": "hdflv2",
				"new_description": "1080P 高码率",
				"display_desc": "1080P",
				"superscript": "高码率",
				"codecs": [
					"av01.0.00M.10.0.110.01.01.01.0",
					"avc1.640033",
					"hev1.1.6.L120.90"
				]
			},
			{
				"quality": 80,
				"format": "flv",
				"new_description": "1080P 高清",
				"display_desc": "1080P",
				"superscript": "",
				"codecs": [
					"av01.0.00M.10.0.110.01.01.01.0",
					"avc1.640032",
					"avc1.640033",
					"hev1.1.6.L120.90"
				]
			},
			{
				"quality": 64,
				"format": "flv720",
				"new_description": "720P 高清",
				"display_desc": "720P",
				"superscript": "",
				"codecs": [
					"av01.0.00M.10.0.110.01.01.01.0",
					"avc1.640028",
					"avc1.640033",
					"hev1.1.6.L120.90"
				]
			},
			{
				"quality": 32,
				"format": "flv480",
				"new_description": "480P 清晰",
				"display_desc": "480P",
				"superscript": "",
				"codecs": [
					"av01.0.00M.10.0.110.01.01.01.0",
					"avc1.64001F",
					"avc1.640033",
					"hev1.1.6.L120.90"
				]
			},
			{
				"quality": 16,
				"format": "flv360",
				"new_description": "360P 流畅",
				"display_desc": "360P",
				"superscript": "",
				"codecs": [
					"av01.0.00M.10.0.110.01.01.01.0",
					"avc1.64001E",
					"avc1.640033",
					"hev1.1.6.L120.90"
				]
			}
		],
		"high_format": null,
		"last_play_time": 35000,
		"last_play_cid": 26494174859,
		"view_info": null
	}
}
```
4. 使用ffmpeg命令将获取到的音视频m4s地址合并成mp4文件
```shell
ffmpeg -i "https://xy112x30x92x2xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859_x1-1-100143.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=118831&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=cos&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=085462&traceid=trhFuRHptJrGDP_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=a0a30653358fa7f61ebb526d7df974ca" -i "https://xy115x231x135x145xy.mcdn.bilivideo.cn:8082/v1/resource/26494174859-1-30280.m4s?agrr=1&build=0&buvid=&bvc=vod&bw=15003&deadline=1732266877&e=ig8euxZM2rNcNbdlhoNvNC8BqJIzNbfqXBvEqxTEto8BTrNvN0GvT90W5JZMkX_YN0MvXg8gNEV4NC8xNEV4N03eN0B5tZlqNxTEto8BTrNvNeZVuJ10Kj_g2UB02J0mN0B5tZlqNCNEto8BTrNvNC7MTX502C8f2jmMQJ6mqF2fka1mqx6gqj0eN0B599M%3D&f=u_0_0&gen=playurlv2&logo=A0020000&mcdnid=50012580&mid=482104881&nbs=1&nettype=0&og=hw&oi=1857594792&orderid=0%2C3&os=mcdn&platform=pc&sign=56b2e2&traceid=trLlAQtUJKuBrW_0_e_N&uipk=5&uparams=e%2Cuipk%2Cnbs%2Cdeadline%2Cgen%2Cos%2Coi%2Ctrid%2Cmid%2Cplatform%2Cog&upsig=458b1bc64d4aa3491a77ac542454dde4" -c copy output.mp4
```
结束：  
==
  <font>注：仅供学习,切勿用于其他用途，由使用人自行承担因此引发的一切法律责任，作者不承担法律责任。</font> <br>
  **喜欢的话，给个star呗**<br>
  **喜欢的话，给个star呗**<br>
  **喜欢的话，给个star呗**<br>
  
  <font color="red">自己可以参考tests/test.php</font> <br>
  都无法使用再提issue
