<div align="center">
  <img width="100px" alt="logo" src="https://img.jiuhunwl.cn/up/2025/05/23/68305545df6d7.png"/></a>
  <p><em>Source code for short video watermark removal and parsing interface</em></p>
<div>
  <a href="https://github.com/OpenListTeam/jiuhunwl/short_videos/main/LICENSE">
    <img src="https://img.shields.io/github/license/jiuhunwl/short_videos" alt="License" />
  </a>
</div>
<div>
</div>
</div>

# Source code for short video watermark removal and parsing interface
| English | [中文](./README.md)
## ！Declaration ！
This project is open source software and is licensed under the MIT license. Any person or organization is free to use, modify and distribute the source code of this project. However, we expressly state that the Project and any derivative works thereof may not be used for any commercial or paid projects. Any violation of this statement will be considered an infringement of the license of this project. We encourage everyone to contribute and share their code in accordance with the open source ethos and licenses.

## 🚀 Project Introduction
This tool is used to parse the video link of the short video platform and obtain the detailed information of the video, such as the video address, cover image, and author information.
## Project environment
PHP8.0
## Functional Features
- 支持多种短视频平台的链接解析
- 快速获取视频相关信息
- 返回结构化的 JSON 数据

## 📦 Installation and Deployment

### 1. Download the code



```
git clone https://github.com/jiuhunwl/short_videos.git

cd short_videos
```
### 2. Use directly (no installation required)

Upload `xxx.php` to a web server and access it via URL：
```
http://Your server address/xxx.php?url=Target link
```
### 3. Interface Directory

- [x] [douyin.php](short_videos/api/douyin.php): Script for removing watermarks from Douyin video atlases.
- [x] [kuaishou.php](short_videos/api/kuaishou.php): Script for removing watermarks from Kuaishou short videos.
- [x] [ksimg.php](short_videos/api/ksimg.php): Script for parsing Kuaishou atlases.
- [x] [xhs.php](short_videos/api/xhs.php): Script for parsing Xiaohongshu videos.
- [x] [xhsimg.php](short_videos/api/xhsimg.php): Script for parsing Xiaohongshu images.
- [x] [xhsjx.php](short_videos/api/xhsjx.php): Script for removing watermarks from Xiaohongshu videos and atlases.
- [x] [qsmusic.php](short_videos/api/qsmusic.php): Script for parsing Qishui Music.
- [x] [pipigx.php](short_videos/api/pipigx.php): Script for removing watermarks from Pipigx videos.
- [x] [ppxia.php](short_videos/api/ppxia.php): Script for removing watermarks from Pipixia videos.
- [x] [bilibili.php](short_videos/api/bilibili.php): Script for removing watermarks from Bilibili videos.
- [x] [weibo.php](short_videos/api/weibo.php): Script for removing watermarks from Weibo videos (interface version).
- [x] [weibo_v.php](short_videos/api/weibo_v.php): Script for removing watermarks from Weibo videos.

### Request Parameters

| Parameter Name | Type | Description | Required |
| ---- | ---- | ---- | ---- |
| url | String | Video link from a short video platform | Yes |

### Request Example
```plaintext
https://api.bugpk.com/api/xxx.php?url=https://xxx.xxx/xxx
```
### 请求结果
```plaintext
{
    "code": 200,
    "msg": "Parsing successful",
    "data": {
        "author": "Author's name",
        "authorID": "Author's ID",
        "title": "Title",
        "desc": "Detailed description",
        "avatar": "Author's avatar",
        "cover": "Work's cover",
        "url": "Direct link to the work",
        "imgurl": [Atlas links]
    }
}
```
## 📮 Contact Us

**Author**：JH-Ahua

**Interface demonstration official website**：[https://api.bugpk.com/](https://api.bugpk.com/)

**Feedback email**：[admin@bugpk.com](mailto:admin@bugpk.com)

**GitHub**：[https://github.com/jiuhunwl](https://github.com/jiuhunwl)
