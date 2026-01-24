<div align="center">
  <img width="100px" alt="logo" src="https://img.jiuhunwl.cn/up/2025/05/23/68305545df6d7.png"/>
  <h1>Short Video Watermark Removal & Parsing API</h1>
  <p><em>Source code for short video watermark removal and parsing interface</em></p>
  <div>
    <a href="https://github.com/OpenListTeam/jiuhunwl/short_videos/main/LICENSE">
      <img src="https://img.shields.io/github/license/jiuhunwl/short_videos" alt="License" />
    </a>
    <a href="https://php.net">
      <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP Version" />
    </a>
    <a href="https://github.com/jiuhunwl/short_videos">
      <img src="https://img.shields.io/github/stars/jiuhunwl/short_videos?style=social" alt="GitHub Stars" />
    </a>
  </div>
  <br />
  <div>
    <a href="#features">Features</a>
    •
    <a href="#supported-platforms">Platforms</a>
    •
    <a href="#installation">Installation</a>
    •
    <a href="#usage">Usage</a>
    •
    <a href="#api-reference">API Reference</a>
    •
    <a href="#contact">Contact</a>
  </div>
</div>

---

## 📋 Table of Contents

- [About](#about)
- [⚠️ Important Notice](#%EF%B8%8F-important-notice)
- [✨ Features](#features)
- [🌐 Supported Platforms](#supported-platforms)
- [📦 Installation](#installation)
- [🚀 Usage](#usage)
- [📖 API Reference](#api-reference)
- [🤝 Contributing](#contributing)
- [📄 License](#license)
- [📮 Contact](#contact)

---

## 🎯 About

This tool is designed to parse video links from various short video platforms and extract detailed information such as:

- Direct video URLs (without watermarks)
- Cover images
- Author information
- Video metadata

All responses are returned in structured JSON format for easy integration.

---

## ⚠️ Important Notice

This project is open-source software licensed under the MIT License. Any individual or organization is free to use,
modify, and distribute the source code.

**However, we explicitly state that this project and any derivative works may NOT be used for commercial or paid
projects.**

Any violation of this statement will be considered an infringement of the project's license terms.

We encourage everyone to contribute and share code in accordance with open-source ethics and licensing terms.

---

## ✨ Features

- **Multi-Platform Support**: Parse video links from various short video platforms
- **Watermark Removal**: Get direct video URLs without watermarks
- **Fast Performance**: Quick response times for parsing requests
- **Structured Data**: Returns well-formatted JSON data
- **Easy Integration**: Simple API interface for seamless integration
- **No Installation Required**: Can be used directly on any PHP server

---

## 🌐 Supported Platforms

| Platform                          | API File       | Status   |
|-----------------------------------|----------------|----------|
| **Douyin** (TikTok China)         | `douyin.php`   | ✅ Active |
| **Kuaishou**                      | `kuaishou.php` | ✅ Active |
| **Kuaishou Images**               | `ksimg.php`    | ✅ Active |
| **Xiaohongshu** (Little Red Book) | `xhs.php`      | ✅ Active |
| **Xiaohongshu Images**            | `xhsimg.php`   | ✅ Active |
| **Xiaohongshu Live**              | `xhsjx.php`    | ✅ Active |
| **Qishui Music**                  | `qsmusic.php`  | ✅ Active |
| **Pipigx**                        | `pipigx.php`   | ✅ Active |
| **Pipixia**                       | `ppxia.php`    | ✅ Active |
| **Bilibili**                      | `bilibili.php` | ✅ Active |
| **Weibo** (Interface Version)     | `weibo.php`    | ✅ Active |
| **Weibo**                         | `weibo_v.php`  | ✅ Active |

---

## 📦 Installation

### Requirements

- **PHP 8.0** or higher
- Web server (Apache/Nginx)
- No additional dependencies required!

### 1. Download the Code

```bash
git clone https://github.com/jiuhunwl/short_videos.git
cd short_videos
```

### 2. Deploy to Server

Upload the PHP files to your web server. No installation or configuration needed!

---

## 🚀 Usage

### Basic Usage

Access the API directly via URL:

```plaintext
http://your-server-domain/api/xxx.php?url=VIDEO_LINK
```

### Example Request

```plaintext
https://api.bugpk.com/api/douyin.php?url=https://v.douyin.com/xxxx/
```

### Example Response

```json
{
    "code": 200,
    "msg": "Parsing successful",
    "data": {
      "author": "Author Name",
      "authorID": "123456789",
      "title": "Video Title",
      "desc": "Video description content",
      "avatar": "https://example.com/avatar.jpg",
      "cover": "https://example.com/cover.jpg",
      "url": "https://example.com/video.mp4",
      "imgurl": [
        "https://example.com/image1.jpg",
        "https://example.com/image2.jpg"
      ]
    }
}
```

---

## 📖 API Reference

### Request Parameters

| Parameter | Type   | Description                          | Required |
|-----------|--------|--------------------------------------|----------|
| `url`     | String | Video link from short video platform | ✅ Yes    |

### Response Format

| Field           | Type    | Description                            |
|-----------------|---------|----------------------------------------|
| `code`          | Integer | Response status code (200 = success)   |
| `msg`           | String  | Response message                       |
| `data`          | Object  | Video data object                      |
| `data.author`   | String  | Author's name                          |
| `data.authorID` | String  | Author's unique ID                     |
| `data.title`    | String  | Video title                            |
| `data.desc`     | String  | Video description                      |
| `data.avatar`   | String  | Author's avatar URL                    |
| `data.cover`    | String  | Video cover image URL                  |
| `data.url`      | String  | Direct video URL (without watermark)   |
| `data.imgurl`   | Array   | Array of image URLs (for image albums) |

### Status Codes

| Code  | Description                |
|-------|----------------------------|
| `200` | Parsing successful         |
| `400` | Invalid request parameters |
| `404` | Video not found            |
| `500` | Server error               |

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit issues and pull requests.

### How to Contribute

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see
the [LICENSE](https://github.com/OpenListTeam/jiuhunwl/short_videos/main/LICENSE) file for details.

---

## 📮 Contact

**Author**: JH-Ahua

**Official Demo Website**: [https://api.bugpk.com/](https://api.bugpk.com/)

**Feedback Email**: [admin@bugpk.com](mailto:admin@bugpk.com)

**GitHub**: [https://github.com/jiuhunwl](https://github.com/jiuhunwl)

---

<div align="center">
  <p>⭐ If you find this project useful, please give it a star!</p>
</div>

---

*[中文](./README.md)*
