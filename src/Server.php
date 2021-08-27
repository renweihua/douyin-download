<?php

declare(strict_types = 1);

namespace Cnpscy\DouyinDownload;

abstract class Server
{
    const SERVER_URL = 'https://www.iesdouyin.com/web/api/v2/';

    protected $http;

    public function __construct()
    {
        $this->http = new Http;
    }

    /**
     * 获取会员详情的API的URL
     *
     * @param  string  $sec_uid
     *
     * @return string
     */
    public function getUserUrlByUid(string $sec_uid): string
    {
        return self::SERVER_URL . 'user/info/?sec_uid=' . $sec_uid;
    }

    /**
     * 获取会员的视频列表的API的URL
     *
     * @param  string  $sec_uid
     * @param  int     $max_cursor
     *
     * @return string
     */
    public function getVideosUrlByUid(string $sec_uid, int $max_cursor = 0): string
    {
        return self::SERVER_URL . 'aweme/post/?sec_uid=' . $sec_uid . '&count=2000&max_cursor=' . $max_cursor;
    }

    /**
     * 通过URL获取会员的Uid
     *
     * @param  string  $url
     *
     * @return string
     * @throws \Exception
     */
    public function getUidByUrl(string $url): string
    {
        $content = htmlspecialchars($this->http->setMaxFollow(0)->fetch($url));
        preg_match('/(?<=sec_uid=)[A-Za-z0-9-_]+/', $content, $sec_uid);
        return current($sec_uid);
    }

    abstract public function getVideosByUid(string $sec_uid): array;

    abstract public function getUserInfoByUid(string $sec_uid): array;

    public function json_encode($data, string $options = '')
    {
        return json_encode($data, empty($options) ? (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $options);
    }

    function get_url($url) {
        $Header=array("User-Agent:Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$Header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $result =  curl_exec($ch);

        curl_close ($ch);
        $result=mb_convert_encoding($result, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        return $result;
    }
}