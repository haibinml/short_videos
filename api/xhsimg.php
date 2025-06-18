<?php
/**
*@Author: JH-Ahua
*@CreateTime: 2025/6/16 下午4:17
*@email: admin@bugpk.com
*@blog: www.jiuhunwl.cn
*@Api: api.bugpk.com
*@tip: 小红书图文解析
*/
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
    // 构造请求数据
    $cookie = "xhsTrackerId=e6018ab9-6936-4b02-cb65-a7f9f9e22ea0; xhsuid=y2PCwPFU9GCQnJH8; timestamp2=20210607d2293bcc8dcad65834920376; timestamp2.sig=QFn2Zv9pjUr07KDlnh886Yq43bZxOaT6t3WCzZdzcgM; xhsTracker=url=noteDetail&xhsshare=CopyLink; extra_exp_ids=gif_exp1,ques_exp2'";
    $domain = parse_url($url);
    if($domain['host']=="www.xiaohongshu.com"){
        $loc = $url;
       // 定义正则表达式模式
        $pattern = '/explore\/([a-zA-Z0-9]+)/';
        // 执行匹配
        if (preg_match($pattern, $loc, $matches)) {
            $id=$matches[1]; // 返回捕获的笔记ID

        }
    }else{
        $id = extractId($url);
        $loc = get_headers($url, 1)["Location"] ?? $url;
    }
    // 发送请求获取视频信息
    $response = get_curl($loc,$cookie);
    if (!$response) {
        return output(400, '请求失败,请检查图文是否失效');
    }

    // 优化正则表达式
    $pattern = '/<script>\s*window.__INITIAL_STATE__\s*=\s*({[\s\S]*?})<\/script>/is';
    if (preg_match($pattern, $response, $matches)) {
        $jsonData = $matches[1];
        // 将 undefined 替换为 null
        $jsonData = str_replace('undefined', 'null', $jsonData);
        $imagejson = json_decode($jsonData, true);
        $imageData = $imagejson["note"]['noteDetailMap'][$id]['note'];
        $imgurl = [];
        foreach ($imageData['imageList'] as $item) {
            // 检查当前元素是否包含 url_list 标签
            if (isset($item['urlDefault'])) {
                // 将 url_list 的第一个值添加到 $imgurl 数组中
                $imgurl[] = $item['urlDefault'];
            }
        }
        if ($jsonData) {
            if ($imageData) {
                $data = [
                    'author' => $imageData['user']['nickname'] ?? '',
                    'userId' => $imageData['user']['userId'] ?? '',
                    'title' => $imageData['title'] ?? '',
                    'desc' => $imageData['desc'] ?? '',
                    'avatar' => $imageData['user']['avatar'] ?? '',
                    'cover' => $imageData['imageList'][0]['urlPre'] ?? '',
                    'imgurl' => $imgurl
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

function get_curl($url, $cookie)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
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
    $headers = @get_headers($url, true);
    if ($headers === false) {
        // 如果获取头信息失败，直接使用原始 URL
        $loc = $url;
    } else {
        // 处理重定向头可能是数组的情况
        if (isset($headers['Location']) && is_array($headers['Location'])) {
            $loc = end($headers['Location']);
        } else {
            $loc = $headers['Location'] ?? $url;
        }
    }

    // 确保 $loc 是字符串
    if (!is_string($loc)) {
        $loc = strval($loc);
    }

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
        preg_match($pattern, $loc, $matches);
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
        $url = urldecode($encodedUrl);
    }
} else {
    $url = $_POST['url']?? null;
}

// 检查必要参数
if (!$url) {
    header('Content-Type: application/json');
    echo json_encode(['error' => '必须提供url参数','Auther' => 'BugPk','website' => 'https://api.bugpk.com/'], 480);
    return;
} else {
    $domain = parse_url($url);
    if($domain['host']=="xhs.com"){
        $parts = explode('/', $url);
        $url = 'http://xhslink.com/a/'.$parts[4]; 
    }
    echo xhsimg($url);
}
?>
