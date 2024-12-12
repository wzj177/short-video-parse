<?php

use Wzj\ShortVideoParse\VideoManager;

require __DIR__ . '/../vendor/autoload.php';


$result = VideoManager::Bili()->makeQrcodeLoginUrl();

$loginResult = VideoManager::Bili()->qrcodeLogin([
    'oauthKey' => $result['oauthKey'],
    'gourl' => 'https://www.bilibili.com'
]);

var_dump($loginResult);