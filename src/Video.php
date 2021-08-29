<?php

declare(strict_types = 1);

namespace Cnpscy\DouyinDownload;

class Video extends Server
{
    public function getUserInfoByUid(string $sec_uid): array
    {
        $this->http->randUserAgent();
        exit;
    }

    public function getVideosByUid(
        string $sec_uid,
        bool $open_download = false,
        string $path_file_folder = '',
        int $sleep = 0
    ): array
    {
        $author = [];
        $videos_list  = $response_lists = [];
        $download_nums = $max_cursor = 0;

        do{
            sleep($sleep);

            $url = $this->getVideosUrlByUid($sec_uid, $max_cursor);

            $response  = json_decode($this->http->setMethod('GET')->setMaxFollow(1)->fetch($url), true);
            if (!$response) {
                break;
            }

            // 获取作者的信息
            if ($response['aweme_list'] && empty($author)){
                $aweme_author = current($response['aweme_list'])['author'];
                $author = [
                    'sec_uid' => $aweme_author['sec_uid'],
                    'nick_name' => $aweme_author['nickname'], // 昵称
                    'signature' => $aweme_author['signature'], // 签名
                    'uid' => $aweme_author['uid'],
                    'follower_count' => $aweme_author['follower_count'], // 关注数量
                    'total_favorited' => $aweme_author['total_favorited'],
                    'unique_id' => $aweme_author['unique_id'],
                    'avatar_thumb' => current($aweme_author['avatar_thumb']['url_list']), // 头像
                ];

                // 开启下载，创建文件夹
                if ($open_download){
                    $path_file_folder .= $author['nick_name'] . '-' . $author['unique_id'] . '/';
                    if (!is_dir($path_file_folder)) {
                        mkdir($path_file_folder, 0777, true);
                    }
                }
            }
            if (isset($author['nick_name']) && !empty($author['nick_name'])){
                $author['nick_name'] = current($response['aweme_list'])['author']['nickname'];
            }
            // 是否还有更多数据
            $has_more = empty($response['aweme_list']) ? false : ($response['has_more'] ?? false);
            // 下一页的标识
            $max_cursor = $response['max_cursor'];

            foreach ($response['aweme_list'] as $item){
                $video = [
                    'sec_uid' => $item['author']['sec_uid'],
                    'uid' => $item['author']['uid'],
                    'aweme_id' => $item['aweme_id'], // 视频Id
                    'cover' => current($item['video']['origin_cover']['url_list']), // 封面图
                    'video_path' => $item['video']['play_addr']['url_list'][0] ?? $item['video']['play_addr_lowbr']['url_list'][0],
                    'duration' => $item['video']['duration'], // 时长
                    'width' => $item['video']['width'],
                    'height' => $item['video']['height'],
                    'ratio' => $item['video']['ratio'],
                    'statistics' => $item['statistics'],
                    'desc' => $item['desc'],
                    'images' => $item['images'],
                    'long_video' => $item['long_video'],
                    'real_video_path' => '',
                ];

                // 开启下载，下载文件
                if ($open_download){
                    $file_name = empty($video['desc']) ? $video['sec_uid'] : $video['desc'];
                    $file_name = str_replace(['/','//','\\',], '', $file_name);
                    $path_file_name = $path_file_folder . '/' . $file_name .'.mp4';
                    // 下载文件
                    if ($video['video_path'] && !is_file($path_file_name)){
                        file_put_contents($path_file_name, fopen($video['video_path'], 'r'));
                        ++$download_nums;
                    }
                    $video['real_video_path'] = $path_file_folder;
                }

                $response_lists[] = $response;
                $videos_list[] = $video;
            }
        }while($has_more);

        return [
            'total' => count($videos_list),
            'author' => $author,
            'download_nums' => $download_nums,
            'file_folder' => $path_file_folder,
            'list' => $videos_list,
        ];
    }
}