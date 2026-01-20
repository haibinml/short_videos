<?php
/**
 * XiaohongshuParser - 小红书解析类
 * 整合图文、视频、实况解析
 */

class XiaohongshuParser
{
    private $headers;
    private $cookie;
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0';

    public function __construct()
    {
        $this->headers = [
            'User-Agent: ' . $this->userAgent,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
        ];
    }

    /**
     * 统一输出函数
     */
    private function output($code, $msg, $data = [])
    {
        return json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ], 480); // 保持原有的json选项 (JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)
    }

    /**
     * 设置Cookie
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * 发送HTTP请求
     */
    private function request($url, $customHeaders = [], $returnHeader = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $headers = array_merge($this->headers, $customHeaders);
        if ($this->cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($returnHeader) {
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return false;
        }
        return $response;
    }

    /**
     * 获取重定向后的真实链接
     */
    private function getRealUrl($url)
    {
        // 方案一：优先使用 get_headers，因为它能处理某些 cURL 无法处理的 302 跳转
        // 设置 User-Agent 模拟浏览器
        stream_context_set_default([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: " . $this->userAgent
            ]
        ]);

        // 抑制错误输出，防止域名无法解析时报错
        $headers = @get_headers($url, 1);

        if (isset($headers['Location'])) {
            $location = $headers['Location'];
            // 如果是数组（多次跳转），通常第一个就是目标链接，后续可能是跳转到登录页
            if (is_array($location)) {
                // 优先寻找包含 item/note/explore 等 ID 特征的链接
                foreach ($location as $loc) {
                    if ($this->extractId($loc)) {
                        return $loc;
                    }
                }
                // 如果没找到特征链接，返回第一个
                return $location[0];
            }
            return $location;
        }

        // 方案二：如果 get_headers 失败，尝试 cURL 作为备选
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // 只需要头信息
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_exec($ch);
        $realUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        return $realUrl ?: $url;
    }

    /**
     * 提取ID
     */
    private function extractId($url)
    {
        $patterns = [
            '/discovery\/item\/([a-zA-Z0-9]+)/',
            '/explore\/([a-zA-Z0-9]+)/',
            '/item\/([a-zA-Z0-9]+)/',
            '/note\/([a-zA-Z0-9]+)/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * 主解析方法
     */
    public function parse($url)
    {
        if (empty($url)) {
            return $this->output(400, '请输入小红书链接');
        }

        // 预处理：将 xhs.com 替换为 xhslink.com
        // 某些情况下分享链接可能是 xhs.com，直接访问可能无法正确跳转，需转换为 xhslink.com
        $url = str_replace('xhs.com', 'xhslink.com', $url);

        // 处理链接逻辑：参考 xhsjx.php
        $domain = parse_url($url, PHP_URL_HOST);
        if ($domain == 'www.xiaohongshu.com') {
            $id = $this->extractId($url);
        } else {
            // 如果不是主域名，则认为是短链接或需要重定向的链接
            $url = $this->getRealUrl($url);
            $id = $this->extractId($url);
        }

        if (!$id) {
            // 尝试从 query 参数中再次查找 (某些情况下 URL 结构可能不同)
            // 比如 ?business_id=xxx
            return $this->output(400, '链接格式错误，无法提取ID。处理后的链接: ' . $url);
        }

        // 第一次请求，尝试获取页面内容或token
        $response = $this->request($url);
        if (!$response) {
            return $this->output(500, '请求失败');
        }

        // 尝试直接匹配JSON
        $data = $this->extractJson($response, $id);

        // 如果直接匹配失败，尝试xhslive.php中的高级策略（获取token后请求API）
        if (!$data) {
            $token = '';
            if (preg_match('/token=(.*?)&/', $response, $matches)) {
                $token = $matches[1];
            } elseif (preg_match('/"xsec_token":\s*"([^"]+)"/', $response, $matches)) {
                $token = $matches[1];
            }

            if ($token) {
                $apiUrl = "https://www.xiaohongshu.com/discovery/item/{$id}?app_platform=android&ignoreEngage=true&app_version=8.69.5&share_from_user_hidden=true&xsec_source=app_share&type=video&xsec_token={$token}";
                $apiResponse = $this->request($apiUrl);
                if ($apiResponse) {
                    $data = $this->extractJson($apiResponse, $id);
                }
            }
        }

        if ($data) {
            return $this->output(200, '解析成功', $data);
        }

        return $this->output(404, '解析失败，未找到有效内容');
    }

    /**
     * 从HTML中提取并解析JSON
     */
    private function extractJson($html, $id)
    {
        $pattern = '/<script>\s*window.__INITIAL_STATE__\s*=\s*({[\s\S]*?})<\/script>/is';
        if (preg_match($pattern, $html, $matches)) {
            $jsonStr = $matches[1];
            $jsonStr = str_replace('undefined', 'null', $jsonStr);
            $json = json_decode($jsonStr, true);

            if (!$json) return null;

            // 尝试获取笔记详情
            // 路径1: note -> noteDetailMap -> id -> note
            $note = $json['note']['noteDetailMap'][$id]['note'] ?? null;

            // 路径2: noteData -> data -> noteData
            if (!$note) {
                $note = $json['noteData']['data']['noteData'] ?? null;
            }

            if ($note) {
                return $this->formatNoteData($note);
            }
        }
        return null;
    }

    /**
     * 格式化笔记数据
     */
    private function formatNoteData($note)
    {
        $type = $note['type'] ?? 'unknown';
        // 标准化类型: normal -> image
        if ($type === 'normal') {
            $type = 'image';
        }

        $result = [
            'type' => $type, // video, image, live
            'title' => $note['title'] ?? '',
            'desc' => $note['desc'] ?? '',
            'author' => [
                'name' => $note['user']['nickname'] ?? '',
                'id' => $note['user']['userId'] ?? '',
                'avatar' => $note['user']['avatar'] ?? '',
            ],
            // 优先使用 urlDefault (通常是无水印高清图)，如果不存在则回退到 urlPre
            'cover' => $note['imageList'][0]['urlDefault'] ?? ($note['imageList'][0]['urlPre'] ?? ($note['cover']['url'] ?? '')),
            'url' => null,
            'images' => [],
            'live_photo' => [] // 实况图
        ];

        // 处理视频
        if ($result['type'] == 'video') {
            $videoUrl = null;
            $videoBackup = null;

            // 提取所有可用的视频流
            $streams = [];

            // 优先收集 h265 (通常无水印且画质更好)
            if (isset($note['video']['media']['stream']['h265']) && is_array($note['video']['media']['stream']['h265'])) {
                foreach ($note['video']['media']['stream']['h265'] as $stream) {
                    $streams[] = $stream;
                }
            }

            // 其次收集 h264
            if (isset($note['video']['media']['stream']['h264']) && is_array($note['video']['media']['stream']['h264'])) {
                foreach ($note['video']['media']['stream']['h264'] as $stream) {
                    $streams[] = $stream;
                }
            }

            // 如果有可用流，按画质排序
            if (!empty($streams)) {
                // 按平均码率降序排序，码率越高画质通常越好
                usort($streams, function($a, $b) {
                    $bitrateA = $a['avgBitrate'] ?? ($a['videoBitrate'] ?? 0);
                    $bitrateB = $b['avgBitrate'] ?? ($b['videoBitrate'] ?? 0);
                    return $bitrateB - $bitrateA;
                });

                // 取最高画质为主链接
                $videoUrl = $streams[0]['masterUrl'] ?? null;

                // 取第二高画质为备用链接 (如果存在)
                if (count($streams) > 1) {
                    $videoBackup = $streams[1]['masterUrl'] ?? null;
                }
            }

            // 兜底逻辑：如果上面的流都没获取到
            if (!$videoUrl && isset($note['video']['consumer']['originVideoKey'])) {
                $videoUrl = 'http://sns-video-bd.xhscdn.com/' . $note['video']['consumer']['originVideoKey'];
            }

            $result['url'] = $videoUrl;
            $result['video_backup'] = $videoBackup; // 新增备用链接字段
        }

        // 处理图片和实况
        if (!empty($note['imageList'])) {
            foreach ($note['imageList'] as $img) {
                // 普通图片
                if (isset($img['urlDefault'])) {
                    $result['images'][] = $img['urlDefault'];
                } elseif (isset($img['urlPre'])) {
                    $result['images'][] = $img['urlPre']; // 兜底
                }

                // 实况图 (Live Photo)
                // 实况图通常在 imageList 的 item 中有 stream 字段 (h264等)
                // 参考 xhslive.php 的逻辑
                $liveVideoUrl = null;
                if (isset($img['stream']['h264'][0]['masterUrl'])) {
                    $liveVideoUrl = $img['stream']['h264'][0]['masterUrl'];
                } elseif (isset($img['stream']['h265'][0]['masterUrl'])) {
                    $liveVideoUrl = $img['stream']['h265'][0]['masterUrl'];
                }

                if ($liveVideoUrl) {
                    $result['live_photo'][] = [
                        'image' => $img['urlDefault'] ?? '',
                        'video' => $liveVideoUrl
                    ];
                }
            }

            // 如果提取到了实况视频，修正类型为实况
            if (!empty($result['live_photo'])) {
                $result['type'] = 'live';
            }
        }

        return $result;
    }
}
