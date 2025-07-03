# 💳 Klump Product Ads - WordPress Plugin

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-3.0%2B-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

> 🚀 **Transform your WooCommerce store with Klump's "Buy Now, Pay Later" solution**  
> Boost conversions by offering flexible payment options with engaging YouTube video demonstrations.

## 📖 Table of Contents

- [✨ Features](#-features)
- [🔧 Installation](#-installation)
- [⚙️ Configuration](#️-configuration)
- [🎥 YouTube Modal Setup](#-youtube-modal-setup)
- [🎨 Customization](#-customization)
- [📱 Screenshots](#-screenshots)
- [🛠️ Technical Requirements](#️-technical-requirements)
- [🔗 Klump Resources](#-klump-resources)
- [💡 Support](#-support)
- [📄 License](#-license)

## ✨ Features

### 💰 **Core Payment Features**
- 🛒 **Buy Now, Pay Later Integration** - Seamless Klump payment options
- 📊 **Flexible Installments** - Split payments into manageable chunks
- 💳 **Multiple Payment Methods** - Support for various payment channels
- 🔒 **Secure Transactions** - Bank-grade security with Klump's infrastructure

### 🎥 **YouTube Video Modal** *(NEW)*
- 📺 **Interactive Video Demos** - Engage customers with product videos
- 🎬 **YouTube Integration** - Support for all YouTube URL formats
- 📱 **Responsive Design** - Perfect viewing on all devices
- ⌨️ **Keyboard Navigation** - Accessible controls (ESC to close)

### 🎨 **Design & Customization**
- 🌈 **Custom Color Schemes** - Match your brand colors
- 🖼️ **Custom Icons & Logos** - Upload your own payment icons
- 📐 **Responsive Layout** - Mobile-first design approach
- ✨ **Smooth Animations** - Engaging user interactions

### ⚙️ **Advanced Configuration**
- 🔐 **API Integration** - Secure Klump merchant key setup
- 💱 **Multi-Currency Support** - NGN, USD, EUR, GBP
- 🏷️ **Dynamic Pricing** - Automatic product price detection
- 📝 **Custom Text Content** - Personalize all display text

## 🔧 Installation

### 📥 **Method 1: WordPress Admin Dashboard**

1. **Download the Plugin**
   ```bash
   git clone https://github.com/willy4opera/klumps_ads.git
   ```

2. **Upload to WordPress**
   - Go to `WordPress Admin → Plugins → Add New → Upload Plugin`
   - Select the downloaded ZIP file
   - Click `Install Now` → `Activate`

### 📂 **Method 2: Manual Installation**

1. **Upload Files**
   ```bash
   # Upload to your WordPress installation
   /wp-content/plugins/klump-product-ads/
   ```

2. **Activate Plugin**
   - Go to `WordPress Admin → Plugins`
   - Find "Klump Product Ads" and click `Activate`

### ✅ **Post-Installation**

1. **Verify WooCommerce** - Ensure WooCommerce is active
2. **Check Requirements** - Confirm PHP 7.4+ and WordPress 5.0+
3. **Configure Settings** - Navigate to `Settings → Klump Product Ads`

## ⚙️ Configuration

### 🔑 **Step 1: Get Your Klump API Key**

1. **Create Merchant Account**
   - Visit: [🏪 Klump Merchant Dashboard](https://merchant.useklump.com/)
   - Sign up for a new account or log in

2. **Get API Keys**
   - Navigate to: [🔑 API Keys & Webhooks](https://merchant.useklump.com/settings?tab=API+Keys+Webhooks)
   - Copy your **Public Key** (starts with `klp_pk_`)

### ⚙️ **Step 2: Plugin Configuration**

1. **Access Settings**
   ```
   WordPress Admin → Settings → Klump Product Ads
   ```

2. **Required Settings**
   - ✅ **Enable Plugin**: Check to activate
   - 🔑 **Merchant Public Key**: Paste your Klump public key
   - 💱 **Currency**: Select your store currency
   - 💰 **Default Price**: Set fallback price for demos

3. **Optional Settings**
   - 📝 **Custom Text**: Personalize title and description
   - 🎨 **Colors**: Match your brand theme
   - 🖼️ **Icons**: Upload custom payment method icons

## 🎥 YouTube Modal Setup

### 📹 **Adding Video Content**

1. **Prepare Your Video**
   - Create engaging product demonstration videos
   - Upload to YouTube (public or unlisted)
   - Copy the video URL

2. **Configure in Plugin**
   ```
   WordPress Admin → Settings → Klump Product Ads → YouTube Video Modal
   ```

3. **Supported URL Formats**
   ```
   ✅ https://youtube.com/watch?v=VIDEO_ID
   ✅ https://youtube.com/shorts/VIDEO_ID  
   ✅ https://youtu.be/VIDEO_ID
   ✅ https://youtube.com/embed/VIDEO_ID
   ```

### 🎬 **Video Best Practices**

- 📏 **Duration**: Keep videos under 2 minutes
- 📱 **Mobile-Friendly**: Ensure good mobile viewing experience
- 🔊 **Audio**: Include captions for accessibility
- 💡 **Content**: Focus on payment benefits and process

## 🎨 Customization

### 🌈 **Color Schemes**

Choose from preset themes or create custom colors:

- 🔵 **Default Theme**: Klump brand colors
- 🟣 **Purple Theme**: Professional purple tones  
- 🔷 **Blue Theme**: Trust-inspiring blue palette
- 🎨 **Custom**: Define your own color scheme

### 🖼️ **Custom Icons**

Upload custom icons for payment methods:

- 💳 **Card Payments**: Credit/debit card icons
- 📱 **Mobile Money**: Mobile payment icons
- 🏦 **Bank Transfer**: Banking icons
- 👛 **Digital Wallets**: E-wallet icons

### ✨ **Animation Options**

- 🔄 **Smooth Animations**: Elegant transitions
- ⚡ **Fast Animations**: Quick, snappy effects
- 🚫 **No Animations**: For performance or accessibility

## 📱 Screenshots

### 🖥️ **Admin Interface**
```
🎛️ Comprehensive admin dashboard with:
   ├── 🔑 API key configuration
   ├── 🎥 YouTube video setup
   ├── 🎨 Visual customization
   ├── 🖼️ Icon management
   └── 👁️ Live preview
```

### 🛒 **Frontend Display**
```
🏪 Product page integration:
   ├── 📍 Positioned after product title
   ├── 💳 Payment installment details
   ├── 🎥 Clickable video modal
   ├── 📱 Mobile-responsive design
   └── ✨ Smooth animations
```

### 🎬 **Video Modal**
```
📺 Interactive modal featuring:
   ├── 🎥 YouTube video player
   ├── 📱 Responsive design
   ├── ⌨️ Keyboard controls
   ├── 🔄 Loading states
   └── ❌ Multiple close options
```

## 🛠️ Technical Requirements

### 📋 **Minimum Requirements**

| Component | Version | Status |
|-----------|---------|--------|
| 🔷 WordPress | 5.0+ | ✅ Required |
| 🛒 WooCommerce | 3.0+ | ✅ Required |
| 🐘 PHP | 7.4+ | ✅ Required |
| 🗄️ MySQL | 5.6+ | ✅ Required |

### 🌐 **Browser Compatibility**

| Browser | Desktop | Mobile | Status |
|---------|---------|--------|--------|
| 🌐 Chrome | ✅ Latest | ✅ Latest | Fully Supported |
| 🦊 Firefox | ✅ Latest | ✅ Latest | Fully Supported |
| 🧭 Safari | ✅ Latest | ✅ Latest | Fully Supported |
| 🌊 Edge | ✅ Latest | ✅ Latest | Fully Supported |

### ⚡ **Performance Features**

- 🚀 **Lazy Loading**: Assets loaded only when needed
- 📦 **Minified Files**: Optimized for fast loading
- 🗄️ **Caching Friendly**: Compatible with all major caching plugins
- 📱 **Mobile Optimized**: Fast performance on mobile devices

## 🔗 Klump Resources

### 🏢 **Official Links**

- 🌐 **Website**: [useklump.com](https://useklump.com/)
- 🏪 **Merchant Dashboard**: [merchant.useklump.com](https://merchant.useklump.com/)
- 📚 **Documentation**: [docs.useklump.com](https://docs.useklump.com/docs)
- 🔑 **API Keys**: [Get Your Keys](https://merchant.useklump.com/settings?tab=API+Keys+Webhooks)

### 📖 **Developer Resources**

- 📋 **API Documentation**: Complete integration guides
- 🔧 **Webhooks Setup**: Real-time payment notifications
- 🛡️ **Security Guidelines**: Best practices for secure integration
- 💡 **Examples**: Sample implementations and code snippets

## 💡 Support

### 📞 **Contact Information**

- 📧 **Email Support**: [icare@williamsobi.com.ng](mailto:icare@williamsobi.com.ng)
- 📱 **Phone**: 08030756350
- 🌐 **Website**: [useklump.com](https://useklump.com/)

### 🆘 **Getting Help**

1. **📖 Check Documentation**: Review the setup guides first
2. **🔍 Search Issues**: Look for similar problems in GitHub issues
3. **🐛 Report Bugs**: Create detailed issue reports
4. **💡 Feature Requests**: Suggest improvements

### 🛠️ **Troubleshooting**

#### 🚫 **Common Issues**

**Plugin Not Appearing on Product Pages**
```bash
✅ Check: WooCommerce is active
✅ Check: Plugin is enabled in settings
✅ Check: Visit actual product pages (not shop page)
```

**YouTube Video Not Playing**
```bash
✅ Check: URL format is correct
✅ Check: Video is public or unlisted
✅ Check: Browser allows iframe embeds
```

**API Key Validation Failing**
```bash
✅ Check: Key starts with 'klp_pk_'
✅ Check: Key is copied completely
✅ Check: No extra spaces in key
```

## 🤝 Contributing

### 🛠️ **Development Setup**

1. **Clone Repository**
   ```bash
   git clone https://github.com/willy4opera/klumps_ads.git
   cd klumps_ads
   ```

2. **Install Dependencies**
   ```bash
   # No build process required - vanilla PHP/JS
   ```

3. **Development Workflow**
   ```bash
   git checkout -b feature/your-feature-name
   # Make your changes
   git commit -m "Add: your feature description"
   git push origin feature/your-feature-name
   ```

### 📋 **Contribution Guidelines**

- 🔍 **Code Quality**: Follow WordPress coding standards
- 🧪 **Testing**: Test on multiple WordPress/WooCommerce versions
- 📝 **Documentation**: Update README for new features
- 🔒 **Security**: Follow WordPress security best practices

## 📊 Changelog

### 🆕 **Version 2.1.0** - YouTube Modal Integration
- ✨ **NEW**: YouTube video modal functionality
- ✨ **NEW**: Support for all YouTube URL formats
- ✨ **NEW**: Responsive modal design
- ✨ **NEW**: Enhanced admin interface
- 🔧 **IMPROVED**: Better error handling
- 🔧 **IMPROVED**: Mobile responsiveness
- 🐛 **FIXED**: Various UI improvements

### 📈 **Version 2.0.0** - Major Update
- ✨ **NEW**: Enhanced admin interface
- ✨ **NEW**: Custom color schemes
- ✨ **NEW**: Icon management system
- ✨ **NEW**: Live preview functionality
- 🔧 **IMPROVED**: Better API integration
- 🔧 **IMPROVED**: Mobile optimization

## 📄 License

This project is licensed under the **GPL v2 or later**.

```
Klump Product Ads WordPress Plugin
Copyright (C) 2024 Klump

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

<div align="center">

### 🎉 **Ready to boost your sales with Klump?**

[🚀 Get Started](https://merchant.useklump.com/) • [📚 Documentation](https://docs.useklump.com/docs) • [💬 Support](mailto:icare@williamsobi.com.ng)

**Made with ❤️ for WooCommerce merchants**

</div>
