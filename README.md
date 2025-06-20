<div align="center">
  <img width="100px" alt="logo" src="https://api.bugpk.com/logo.png"/></a>
  <p><em>短视频去水印解析接口源码</em></p>
<div>
  <a href="https://github.com/OpenListTeam/jiuhunwl/short_videos/main/LICENSE">
    <img src="https://img.shields.io/github/license/jiuhunwl/short_videos" alt="License" />
  </a>
</div>
<div>
</div>
</div>
---
| 中文 | [English](./README_EN.md) |
|------|---------------------------|

# 短视频去水印解析接口源码

# ！声明 ！
本项目为开源软件，遵循MIT许可证。任何个人或组织均可自由使用、修改和分发本项目的源代码。然而，我们明确声明，本项目及其任何衍生作品不得用于任何商业或付费项目。任何违反此声明的行为都将被视为对本项目许可证的侵犯。我们鼓励大家在遵守开源精神和许可证的前提下，积极贡献和分享代码。

## 🚀 项目简介
本工具用于解析短视频平台的视频链接，获取视频的详细信息，如视频地址、封面图、作者信息等。

## 功能特点
- 支持多种短视频平台的链接解析
- 快速获取视频相关信息
- 返回结构化的 JSON 数据

## 📦 安装与部署

### 1. 下载代码



```
git clone https://github.com/jiuhunwl/short_videos.git

cd short_videos
```
### 2. 直接使用（无需安装）

将 `xxx.php` 上传至 Web 服务器，通过 URL 访问：
```
http://你的服务器地址/xxx.php?url=目标链接
```
### 3.接口目录

- [x] [douyin.php](short_videos/api/douyin.php)：抖音视频图集去水印解析脚本。
- [x] [kuaishou.php](short_videos/api/kuaishou.php)：快手短视频去水印解析脚本。
- [x] [ksimg.php](short_videos/api/ksimg.php)：快手图集解析脚本。
- [x] [xhs.php](short_videos/api/xhs.php)：小红书视频解析脚本。
- [x] [xhsimg.php](short_videos/api/xhsimg.php)：小红书图文解析脚本。
- [x] [xhsjx.php](short_videos/api/xhsjx.php)：小红书视频&图集去水印解析脚本。
- [x] [qsmusic.php](short_videos/api/qsmusic.php)：汽水音乐解析脚本。
- [x] [pipigx.php](short_videos/api/pipigx.php)：皮皮搞笑去水印解析脚本。
- [x] [ppxia.php](short_videos/api/ppxia.php)：皮皮虾去水印解析脚本。
- [x] [bilibili.php](short_videos/api/bilibili.php)：哔哩哔哩视频去水印解析脚本。
- [x] [weibo.php](short_videos/api/weibo.php)：微博视频去水印解析【接口版】脚本。
- [x] [weibo_v.php](short_videos/api/weibo_v.php)：微博视频去水印解析脚本。

### 请求参数

| 参数名 | 类型 | 描述 | 是否必填 |
| ---- | ---- | ---- | ---- |
| url | 字符串 | 短视频平台的视频链接 | 是 |

### 请求示例
```plaintext
https://api.bugpk.com/api/xxx.php?url=https://xxx.xxx/xxx
```
### 请求结果
```plaintext
{
    "code": 200,
    "msg": "解析成功",
    "data": {
        "author": "作者名称",
        "authorID": "作者id",
        "title": "标题",
        "desc": "文案详情",
        "avatar": "作者头像",
        "cover": "作品封面",
        "url": "作品直链",
        "imgurl":[图集链接]
    }
}
```
## 📮 联系我们

**作者**：JH-Ahua

**接口演示官网**：[https://api.bugpk.com/](https://api.bugpk.com/)

**反馈邮箱**：[admin@bugpk.com](mailto:admin@bugpk.com)

**GitHub**：[https://github.com/jiuhunwl](https://github.com/jiuhunwl)
