<div align="center">
  <img width="100px" alt="logo" src="https://api.bugpk.com/logo.png"/></a>
  <p><em>Source code for short video watermark removal and parsing interface</em></p>
<div>
  <a href="https://github.com/OpenListTeam/jiuhunwl/short_videos/main/LICENSE">
    <img src="https://img.shields.io/github/license/jiuhunwl/short_videos" alt="License" />
  </a>
</div>
<div>
</div>
</div>

# ！Declaration ！
本项目为开源软件，遵循MIT许可证。任何个人或组织均可自由使用、修改和分发本项目的源代码。然而，我们明确声明，本项目及其任何衍生作品不得用于任何商业或付费项目。任何违反此声明的行为都将被视为对本项目许可证的侵犯。我们鼓励大家在遵守开源精神和许可证的前提下，积极贡献和分享代码。

## 🚀 Project Introduction
本工具用于解析短视频平台的视频链接，获取视频的详细信息，如视频地址、封面图、作者信息等。

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
