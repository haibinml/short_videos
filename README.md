# 短视频解析工具

## 简介
本工具用于解析短视频平台的视频链接，获取视频的详细信息，如视频地址、封面图、作者信息等。

## 功能特点
- 支持多种短视频平台的链接解析
- 快速获取视频相关信息
- 返回结构化的 JSON 数据

## 使用方法

### 请求参数
| 参数名 | 类型 | 描述 | 是否必填 |
| ---- | ---- | ---- | ---- |
| url | 字符串 | 短视频平台的视频链接 | 是 |

### 请求示例
```plaintext
[https://api.bugpk.com/api/xhs?url=http://xhslink.com/a/63LnyN3WImLb](https://api.bugpk.com/api/xhs?url=http://xhslink.com/a/63LnyN3WImLbb)
### 请求结果
```plaintext
[{
    "code": 200,
    "msg": "解析成功",
    "data": {
        "author": "小美175",
        "authorID": "5e1ecb9c0000000001007ddd",
        "title": "这个舞真的好可爱吖～",
        "desc": "#手势舞[话题]# #梨形身材[话题]# #媚女已刻入骨髓[话题]# #甜妹[话题]# #甜美[话题]# #甜甜的舞怎能少了甜甜的你[话题]# #简单的舞蹈[话题]#",
        "avatar": "https://sns-avatar-qc.xhscdn.com/avatar/1040g2jo31de4udao16005ngupee08vetrqrmph0?imageView2/2/w/120/format/jpg",
        "cover": "https://sns-na-i2.xhscdn.com/b9207166-a4a3-24d8-ff8b-1c40801c670d?imageView2/2/w/1080/format/jpg",
        "url": "http://sns-video-qc.xhscdn.com/stream/1/110/114/01e814d50c6141af010370019691617aea_114.mp4?sign=47c36eb00450b156884ad4f1de0e14cb&t=681c354c"
    }
}]
