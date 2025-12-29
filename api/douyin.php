<?php
/**
 * @Author: JH-Ahua
 * @CreateTime: 2025/10/11 上午12:42
 * @email: admin@bugpk.com
 * @blog: www.jiuhunwl.cn
 * @Api: api.bugpk.com
 * @tip: 抖音视频图集去水印解析
 */
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');
function douyin($url)
{

    // 构造请求数据
    $header = array(
        "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36",
        "Cookie: ");

    // 尝试从 URL 中获取视频 ID
    $id = extractId($url);
    // 检查 ID 是否有效
    if (empty($id)) {
        return array('code' => 400, 'msg' => '无法解析视频 ID');
    }

    // 发送请求获取视频信息
    $response = curl('https://www.douyin.com/user/self?modal_id=' . $id . '&showTab=like', $header);
    $start_str = '<script id="RENDER_DATA" type="application/json">';
    $end_str = '</script>';

// 查找起始位置
    $pos_start = strpos($response, $start_str);
    if ($pos_start === false) {
        // 如果没找到起始位置，返回错误响应，这里调用create_standard_response函数，假设其已正确定义
        return ['code' => 404, 'msg' => '无法找到数据'];
    }

    // 获取起始位置之后的文本内容
    $json_str = substr($response, $pos_start + strlen($start_str));
    // 查找结束位置
    $pos_end = strpos($json_str, $end_str);
    if ($pos_end === false) {
        // 如果没找到结束位置，返回错误响应
        return ['code' => 404, 'msg' => '无法正确提取JSON数据，未找到结束标签'];
    }

// 截取中间的JSON字符串部分
    $json_str = substr($json_str, 0, $pos_end);
// 对URL编码的内容进行解码（类似Python的requests.utils.unquote）
    $json_str = urldecode($json_str);

// 将JSON字符串解析为PHP数组或对象（根据实际JSON结构和后续使用需求）
    $data = json_decode($json_str, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        // 如果JSON解析失败，返回错误响应
        return ['code' => 404, 'msg' => 'JSON解析失败：' . json_last_error_msg()];
    }
    $videoDetail = $data['app']['videoDetail'];

    $imgjson = $videoDetail['images'];
    $images = [];
    $uri = $data['app']['videoDetail']['video']['uri'];
    if (isset($uri) && preg_match('/^[a-zA-Z0-9]+$/', $uri)) {
        $url = $videoDetail['video']['bitRateList'][0]['playAddr'][1]['src'];
        if (empty($url)) {
            $url = 'https://aweme.snssdk.com/aweme/v1/play/?video_id=' . $data['app']['videoDetail']['video']['uri'] . '&ratio=720p&line=0';
        }
    } else {
        $url = [];
        if (is_array($imgjson) && isset($imgjson[0])) {
            // 遍历 JSON 数组
            foreach ($imgjson as $item) {
                // 检查当前元素是否包含 url_list 标签
                if (isset($item['urlList']) && is_array($item['urlList']) && count($item['urlList']) > 0) {
                    // 将 url_list 的第一个值添加到 $imgurl 数组中
                    $images[] = $item['urlList'][0];
                    if (!empty($item['video']['playApi'])) {
                        $url[] = 'https://svproxy.168299.xyz/?proxyurl=' . base64_encode($item['video']['playApi']);
                    }
                }
            }
        }
    }

    $arr = array(
        'code' => 200,
        'msg' => '解析完成',
        'data' => [
            'auther' => $videoDetail['authorInfo']['nickname'],
            'uid' => $videoDetail['authorInfo']['uid'],
            'followerCount' => $videoDetail['authorInfo']['followerCount'],
            'totalFavorited' => $videoDetail['authorInfo']['totalFavorited'],
            'avatar' => $videoDetail['authorInfo']['avatarUri'],
            'title' => $videoDetail['desc'],
            'cover' => $videoDetail['video']['cover'],
            'images' => $images,
            'url' => $url,
            'music' => [
                'title' => $videoDetail['music']['musicName'] ?? null,
                'author' => $videoDetail['music']['ownerNickname'] ?? null,
                'avatar' => $videoDetail['music']['avatarThumb']['urlList'][0] ?? null,
                'url' => $videoDetail['video']['uri'] ?? $videoDetail['music']['playUrl']['uri'] ?? null,
            ],
        ]
    );
    return $arr;
}

function extractId($url)
{
    $headers = get_headers($url, true);
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

    preg_match('/[0-9]+|(?<=video\/)[0-9]+/', $loc, $id);
    return !empty($id) ? $id[0] : null;
}


function curl($url, $header = null, $data = null)
{
    $con = curl_init((string)$url);
    curl_setopt($con, CURLOPT_HEADER, false);
    curl_setopt($con, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($con, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($con, CURLOPT_AUTOREFERER, 1);
    if (isset($header)) {
        curl_setopt($con, CURLOPT_HTTPHEADER, $header);
    }
    if (isset($data)) {
        curl_setopt($con, CURLOPT_POST, true);
        curl_setopt($con, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($con, CURLOPT_TIMEOUT, 5000);
    $result = curl_exec($con);
    if ($result === false) {
        // 处理 curl 错误
        $error = curl_error($con);
        curl_close($con);
        trigger_error("cURL error: $error", E_USER_WARNING);
        return false;
    }
    curl_close($con);
    return $result;
}


// 使用空合并运算符检查 url 参数
$url = $_GET['url'] ?? '';
if (empty($url)) {
    echo json_encode(['code' => 201, 'msg' => 'url为空'], 480);
} else {
    $response = douyin($url);
    if (empty($response)) {
        echo json_encode(['code' => 404, 'msg' => '获取失败'], 480);
    } else {
        echo json_encode($response, 480);
    }
}
?>