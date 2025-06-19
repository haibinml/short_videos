<?php
/**
*@Author: JH-Ahua
*@CreateTime: 2025/6/19 下午8:37
*@email: admin@bugpk.com
*@blog: www.jiuhunwl.cn
*@Api: api.bugpk.com
*@tip: 快手图集解析
*/
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');
function kuaishou($url)
{
    $headers = [
        'Cookie: 自己的cookie',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0'
    ];
    $loc = get_headers($url, 1)['Location'];

    $url = curl($loc,$headers);
    $apolloStatePattern = '/window\.INIT_STATE\s*=\s*(.*?)\<\/script>/s';
    // 匹配包含fid的JSON对象（带转义处理）
    if (preg_match($apolloStatePattern, $url, $matches)) {
        // 处理PHP自动添加的反斜杠
        $jsonString = stripslashes($matches[1]);
        $data = json_decode($jsonString, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            $data = json_decode(cleanInvalidJsonEscapes($jsonString), true);
        }
        // 确保 $data 是数组或对象
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if (strpos($key, 'tusjoh') === 0 && isset($value['fid'])) {
                    $filteredData[$key] = $value;
                }
            }
        } else {
            echo('数据类型非数组');
            $filteredData = []; // 设置默认值为空数组
        }

        // 获取第一个标签的值
        $firstValue = !empty($filteredData) ? json_encode(reset($filteredData)) : '{}';
        $imgjson =json_decode($firstValue,true);
        $img = $imgjson['photo']['ext_params']['atlas']['list']??$imgjson['photo']['coverUrls'][0]['url'];
        $music = 'http://txmov2.a.kwimgs.com'.($imgjson['photo']['ext_params']['atlas']['music']??$imgjson['photo']['music']['audioUrls'][0]['url']);
        $images = array();
        $imgcount = 1;
        if (is_string($img)){
            array_push($images,$img);
        }else{
            for ($i = 0; $i < count($img); $i++) {
                $none = 'http://tx2.a.yximgs.com/' . $img[$i];
                array_push($images, $none);
            }
            $imgcount = count($images);
        }
        if (!empty($img)) {
            $arr = array(
                'code' => 200,
                'msg' => 'success',
                'count' => $imgcount,
                'music' => $music,
                'images' => $images
            );
            return $arr;
        }
    }
}
function cleanInvalidJsonEscapes($jsonStr) {
    // 处理非法Unicode转义序列（如：\ufu3KP → \ufu3 KP → 删除非法部分）
    $jsonStr = preg_replace_callback(
        '/\\\\u([0-9a-fA-F]{0,4})([0-9a-fA-F]*)([^0-9a-fA-F].*?)(?=\\\\u|$)/',
        function($matches) {
            $validPart = '';
            $extraPart = '';

            // 如果前4位是合法十六进制，保留为有效的\uXXXX
            if (strlen($matches[1]) === 4) {
                $validPart = '\\u' . $matches[1];
                $extraPart = $matches[2] . $matches[3];
            }
            // 不足4位但后续有十六进制字符，补足4位
            elseif (strlen($matches[1]) + strlen($matches[2]) >= 4) {
                $hexChars = $matches[1] . $matches[2];
                $validPart = '\\u' . substr($hexChars, 0, 4);
                $extraPart = substr($hexChars, 4) . $matches[3];
            }
            // 完全非法，删除\u
            else {
                $extraPart = $matches[1] . $matches[2] . $matches[3];
            }

            // 保留非转义的字符部分
            return $validPart . (empty($extraPart) ? '' : ' ' . $extraPart);
        },
        $jsonStr
    );

    // 移除剩余的非法转义字符（保留合法的JSON转义）
    $jsonStr = preg_replace(
        '/\\\\([^"\\/bfnrtu])/',
        '$1',
        $jsonStr
    );

    // 修复单引号为双引号
    $jsonStr = str_replace("'", '"', $jsonStr);

    // 移除多余的分号
    $jsonStr = preg_replace('/;([^"]*")/', '$1', $jsonStr);

    return $jsonStr;
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
    return $result;
}
$url = $_GET['url']?? '';
if (empty($url)) {
    echo json_encode(['code' => 201, 'msg' => 'url为空'], 480);
} else {
    $response = kuaishou($url);
    if (empty($response)) {
        echo json_encode(['code' => 404, 'msg' => '获取失败'], 480);
    } else {
        echo json_encode($response, 480);
    }
}
