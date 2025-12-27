<?php
/**
 * @Author: JH-Ahua
 * @CreateTime: 2025/6/16 下午4:17
 * @email: admin@bugpk.com
 * @blog: www.jiuhunwl.cn
 * @Api: api.bugpk.com
 * @tip: 小红书图文解析
 */
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');

// 定义统一的输出函数
function output($code, $msg, $data = [])
{
    return json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ], 480);
}

function xhsimg($url)
{
    $headers = [
        'User-Agent: Dalvik/2.1.0 (Linux; U; Android 14; V2417A Build/UP1A.231005.007) Resolution/1260*2800 Version/8.69.5 Build/8695125 Device/(vivo;V2417A) discover/8.69.5 NetType/WiFi'
    ];
    $domain = parse_url($url);
    if ($domain['host'] == "www.xiaohongshu.com") {
        $id = extractId($url);
    } else {
        $url = get_headers($url, 1)["Location"] ?? $url;
        if (is_array($url)) {
            $url = $url[0];
        }
        $id = extractId($url);
    }
    $response = get_curl($url, $headers);
    preg_match('/token=(.*?)&/', $response, $matches);
    preg_match('/"xsec_token":\s*"([^"]+)"/', $response, $xsec_token_matches);
    // 提取结果
    $token = '';
    if (!empty($matches[1])) {
        $token = $matches[1];
    } elseif (!empty($xsec_token_matches[1])) {
        $token = $xsec_token_matches[1];
    } else {
        return output(201, '获取token失败');
    }
    $detail_header = [
        "cookie: 自行填入ck",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/536.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36 Edg/132.0.0.0"
    ];
    $response = get_curl("https://www.xiaohongshu.com/discovery/item/{$id}?app_platform=android&ignoreEngage=true&app_version=8.69.5&share_from_user_hidden=true&xsec_source=app_share&type=video&xsec_token={$token}", $detail_header);
    // 优化正则表达式
    $pattern = '/<script>\s*window.__INITIAL_STATE__\s*=\s*({[\s\S]*?})<\/script>/is';
    if (preg_match($pattern, $response, $matches)) {
        $jsonData = $matches[1];
        // 将 undefined 替换为 null
        $jsonData = str_replace('undefined', 'null', $jsonData);
        $jsonData = json_decode($jsonData, true);
        $notedata = $jsonData["note"]["noteDetailMap"][$id]["note"];
        $imageListjson = $notedata['imageList'];
        $images = [];
        if ($notedata['type'] == 'video') {
            $url = $notedata['video']['media']['stream']['h264'][0]['masterUrl'] ?? [];
        } else {
            $url = [];

            // 检查$imageListjson是否为非空数组
            if (is_array($imageListjson) && !empty($imageListjson)) {
                // 遍历JSON数组中的每个项目
                foreach ($imageListjson as $item) {
                    // 使用空合并运算符简化嵌套数组的访问和判断
                    $h264Stream = $item['stream']['h264'][0] ?? null;
                    $infoList = $item["infoList"][1]["url"] ?? null;
                    // 检查是否存在有效的h264流信息
                    if (is_array($h264Stream) && !empty($h264Stream['masterUrl'])) {
                        // 获取主URL
                        $masterUrl = $h264Stream['masterUrl'];

                        // 添加到数组（两个数组操作合并）
                        $images[] = $infoList;
                        $url[] = $masterUrl;
                    }
                }
            }
            if (empty($images)) {
                foreach ($notedata['imageList'] as $item) {
                    // 检查当前元素是否包含 url_list 标签
                    if (isset($item['urlDefault'])) {
                        // 将 url_list 的第一个值添加到 $imgurl 数组中
                        $images[] = $item['urlDefault'];
                    }
                }
            }
        }


        if ($jsonData) {
            if ($notedata) {
                $data = [
                    'author' => $notedata['user']['nickname'] ?? '',
                    'userId' => $notedata['user']['userId'] ?? '',
                    'title' => $notedata['title'] ?? '',
                    'desc' => $notedata['desc'] ?? '',
                    'avatar' => $notedata['user']['avatar'] ?? '',
                    'cover' => $notedata['imageList'][0]['urlPre'] ?? '',
                    'type' => $notedata['type'] ?? '',
                    'images' => $images,
                    'url' => $url
                ];
                return output(200, '解析成功', $data);
            } else {
                return output(404, '解析失败，未获取到视频链接');
            }
        } else {
            return output(400, '匹配到的内容不是有效的 JSON 数据');
        }
    } else {
        return output(400, '未找到 JSON 数据');
    }
}

function get_curl($url, $headers = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    // 设置自定义请求头
    if (!empty($headers) && is_array($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    // 保留其他基础配置
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.128 Safari/537.36");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function extractId($url)
{
    // 定义多个正则表达式模式以匹配不同格式的URL
    $patterns = [
        '/discovery\/item\/([a-zA-Z0-9]+)/',     // 原始模式
        '/explore\/([a-zA-Z0-9]+)/',             // 匹配探索页面链接
        '/item\/([a-zA-Z0-9]+)/',                // 匹配项目详情链接
        '/note\/([a-zA-Z0-9]+)/',                // 匹配笔记链接
    ];

    // 依次尝试每个模式
    $id = null;
    foreach ($patterns as $pattern) {
        preg_match($pattern, $url, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            break;
        }
    }

    return $id;
}

// 获取请求参数
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $fullUrl = $_SERVER['REQUEST_URI'];
    // 查找url参数的位置
    $urlParamPos = strpos($fullUrl, 'url=');
    if ($urlParamPos !== false) {
        // 提取url参数后面的所有内容
        $encodedUrl = substr($fullUrl, $urlParamPos + 4);

        // 解码URL
        $url = urldecode($encodedUrl) ?? null;
    }
} else {
    $url = $_POST['url'] ?? null;
}

// 检查必要参数
if (empty($url)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => '必须提供url参数', 'Auther' => 'BugPk', 'website' => 'https://api.bugpk.com/'], 480);
    return;
} else {
    $domain = parse_url($url);
    if ($domain['host'] == "xhs.com") {
        $parts = explode('/', $url);
        $url = 'http://xhslink.com/a/' . $parts[4];
    }
    echo xhsimg($url);
}
?>
