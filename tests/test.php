<?php
    $video = new \Cnpscy\DouyinDownload\Video;

    $url = $_GET['url'] ?? 'https://v.douyin.com/dNdR5W7/';
    $uid = $video->getUidByUrl($url);
    $videos_list = $video->getVideosByUid($uid, true, __DIR__ . '/download/');
    var_dump($videos_list);