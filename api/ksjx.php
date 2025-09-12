<?php
/**
 * 快手链接图片/视频信息提取工具
 *
 * @Author: JH-Ahua
 * @CreateTime: 2025/5/9 上午12:18
 * @email: admin@bugpk.com
 * @blog: www.jiuhunwl.cn
 * @Api: api.bugpk.com
 */

// 跨域与响应头设置
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

// 常量定义 - 集中管理配置信息
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0');
define('COOKIE', '');
define('CURL_TIMEOUT', 5000);
define('JSON_OPTIONS', 480);

/**
 * 处理快手链接，提取图片/视频信息
 *
 * @param string $url 快手链接
 * @return array 提取结果数组
 */
function extractKuaishouInfo(string $url): array
{
    // 获取重定向后的URL
    $redirectUrl = getRedirectedUrl($url);
    if (empty($redirectUrl)) {
        return ['code' => 400, 'msg' => '无法获取有效链接'];
    }

    // 发送请求获取页面内容
    $pageContent = curlRequest($redirectUrl);
    if ($pageContent === false) {
        return ['code' => 500, 'msg' => '页面内容获取失败'];
    }

    // 提取内容ID和类型
    [$contentType, $contentId] = extractContentIdAndType($redirectUrl);
    if (empty($contentId)) {
        return ['code' => 400, 'msg' => '无法识别的链接类型'];
    }

    // 尝试从两种数据模式中提取信息
    $result = extractFromInitState($pageContent)
        ?? extractFromApolloState($pageContent, $contentId, $contentType);

    return $result ?? ['code' => 404, 'msg' => '未找到有效媒体信息'];
}

/**
 * 从URL中提取内容ID和类型
 *
 * @param string $url 重定向后的URL
 * @return array [类型, ID]
 */
function extractContentIdAndType(string $url): array
{
    $patterns = [
        'short-video' => '/short-video\/([^?]+)/',
        'long-video' => '/long-video\/([^?]+)/',
        'photo' => '/photo\/([^?]+)/'
    ];

    foreach ($patterns as $type => $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return [$type, $matches[1]];
        }
    }

    return ['', ''];
}

/**
 * 从window.INIT_STATE提取信息
 *
 * @param string $pageContent 页面内容
 * @return array|null 提取结果或null
 */
function extractFromInitState(string $pageContent): ?array
{
    $pattern = '/window\.INIT_STATE\s*=\s*(.*?)\<\/script>/s';
    if (!preg_match($pattern, $pageContent, $matches)) {
        return null;
    }

    // 清理JSON字符串
    $jsonString = stripslashes($matches[1]);
    $jsonString = str_replace([
        '"{"err_msg":"launchApplication:fail"}"',
        '"{"err_msg":"system:access_denied"}"'
    ], [
        '"err_msg","launchApplication:fail"',
        '"err_msg","system:access_denied"'
    ], $jsonString);
    $jsonString = str_replace('\\', '/', $jsonString);

    // 解析JSON
    $data = json_decode($jsonString, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('INIT_STATE JSON解析错误: ' . json_last_error_msg());
        return null;
    }

    // 过滤有效数据
    $filteredData = filterMediaData($data);
    if (empty($filteredData)) {
        return null;
    }

    $firstItem = reset($filteredData);
    $imageList = $firstItem['photo']['ext_params']['atlas']['list'] ?? [];

    if (empty($imageList)) {
        return null;
    }

    return [
        'code' => 200,
        'msg' => 'success',
        'data' => array(
            'count' => count($imageList),
            'music' => 'http://txmov2.a.kwimgs.com' . ($firstItem['photo']['ext_params']['atlas']['music'] ?? ''),
            'images' => array_map(function ($path) {
                return 'http://tx2.a.yximgs.com/' . $path;
            }, $imageList),
            'api' => 1
        )
    ];
}

/**
 * 从window.__APOLLO_STATE__提取信息
 *
 * @param string $pageContent 页面内容
 * @param string $contentId 内容ID
 * @param string $contentType 内容类型
 * @return array|null 提取结果或null
 */
function extractFromApolloState(string $pageContent, string $contentId, string $contentType): ?array
{
    $pattern = '/window\.__APOLLO_STATE__\s*=\s*(.*?)\<\/script>/s';
    if (!preg_match($pattern, $pageContent, $matches)) {
        return null;
    }

    // 清理Apollo状态数据
    $cleanedData = preg_replace('/function\s*\([^)]*\)\s*{[^}]*}/', ':', $matches[1]);
    $cleanedData = preg_replace('/,\s*(?=}|])/', '', $cleanedData);
    $cleanedData = str_replace(';(:());', '', $cleanedData);

    // 解析JSON
    $apolloState = json_decode($cleanedData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('APOLLO_STATE JSON解析错误: ' . json_last_error_msg());
        return null;
    }

    $videoInfo = $apolloState['defaultClient'] ?? null;
    if (empty($videoInfo)) {
        return null;
    }

    $key = "VisionVideoDetailPhoto:{$contentId}";
    $videoData = $videoInfo[$key] ?? null;
    if (empty($videoData)) {
        return null;
    }

    // 提取视频URL
    $videoUrl = '';
    if ($contentType === 'long-video') {
        $videoUrl = $videoData['manifestH265']['json']['adaptationSet'][0]['representation'][0]['backupUrl'][0] ?? '';
    } else {
        $videoUrl = $videoData['photoUrl'] ?? '';
    }

    if (empty($videoUrl)) {
        return null;
    }

    return [
        'code' => 200,
        'msg' => '解析成功',
        'data' => [
            'title' => $videoData['caption'] ?? '',
            'cover' => $videoData['coverUrl'] ?? '',
            'url' => $videoUrl
        ]
    ];
}

/**
 * 过滤媒体数据
 *
 * @param array $data 原始数据
 * @return array 过滤后的数据
 */
function filterMediaData(array $data): array
{
    $filtered = [];
    foreach ($data as $key => $value) {
        if (strpos($key, 'tusjoh') === 0 && (isset($value['fid']) || isset($value['photo']))) {
            $filtered[$key] = $value;
        }
    }
    return $filtered;
}

/**
 * 发送CURL请求
 *
 * @param string $url 请求URL
 * @param array|null $headers 请求头
 * @param array|null $postData POST数据
 * @return string|false 响应内容或false
 */
function curlRequest(string $url, array $headers = null, array $postData = null)
{
    $ch = curl_init($url);

    // 基础配置
    curl_setopt_array($ch, [
        CURLOPT_HEADER => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_TIMEOUT => CURL_TIMEOUT,
        CURLOPT_USERAGENT => USER_AGENT
    ]);

    // 设置请求头
    if (empty($headers)) {
        $headers = ['Cookie: ' . COOKIE];
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // 设置POST数据
    if (isset($postData)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    $response = curl_exec($ch);

    // 错误处理
    if ($response === false) {
        error_log('CURL请求错误: ' . curl_error($ch) . ' URL: ' . $url);
    }

    curl_close($ch);
    return $response;
}

/**
 * 获取重定向后的URL
 *
 * @param string $url 原始URL
 * @return string|null 重定向后的URL或null
 */
function getRedirectedUrl(string $url): ?string
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => USER_AGENT,
        CURLOPT_TIMEOUT => CURL_TIMEOUT
    ]);

    curl_exec($ch);

    // 获取最终URL
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    // 错误处理
    if (curl_errno($ch)) {
        error_log('重定向获取错误: ' . curl_error($ch) . ' URL: ' . $url);
        $finalUrl = null;
    }

    curl_close($ch);
    return $finalUrl;
}

// 主程序逻辑
$url = $_GET['url'] ?? '';
if (empty($url)) {
    echo json_encode(['code' => 201, 'msg' => 'url为空'], JSON_OPTIONS);
} else {
    $result = extractKuaishouInfo($url);
    echo json_encode($result, JSON_OPTIONS);
}
