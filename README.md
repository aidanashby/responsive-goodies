# Responsive Goodies

A comprehensive WordPress plugin that provides essential responsive design utilities for modern websites.

## Features

### 🔤 Orphan Text Fix
Prevents orphan words at the end of text blocks by automatically adding non-breaking spaces. Configurable to handle 2-10 words with options to exclude specific CSS classes and apply to headings.

### 📱 Device-Based Menu Display
Add device-specific visibility controls to menu items in WordPress menus. Show or hide menu items on desktop, tablet, or mobile devices using Divi's responsive breakpoints.

### 👆 Disable Hover Effects on Touch Devices
Prevents sticky hover states on tablet and mobile devices by disabling hover effects on touch-enabled screens.

### 📏 Prevent Horizontal Scroll
Eliminates horizontal scrolling issues by ensuring all content stays within the viewport width.

### ⬆️ Back to Top Button Control
Control the visibility of Divi's back-to-top button on different devices. Choose which devices should display the scroll-to-top functionality.

## Device Breakpoints

This plugin uses Divi's responsive breakpoints for consistency:
- **Desktop**: 981px and above
- **Tablet**: 768px to 980px  
- **Mobile**: 767px and below

## Installation

1. Download the latest release
2. Upload to your WordPress site via Plugins → Add New → Upload Plugin
3. Activate the plugin
4. Configure settings at Settings → Responsive Goodies

## Automatic Updates

This plugin supports automatic updates via GitHub releases. Your WordPress admin will notify you when new versions are available.

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## Author

**Aidan Ashby**  
[https://lucidrhino.design](https://lucidrhino.design)

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

This is a personal project shared freely with the community. While I've made it available for others to use and fork, I don't provide ongoing support or accept feature requests. Feel free to fork and modify for your own needs.

## Changelog

### Version 0.4.0 (2026-05-24)

**Fixed**
- Changelog modal now renders formatted HTML content from the GitHub Releases API
- Fixed broken markdown-to-HTML ul-wrapping regex in convert_markdown_to_html()
- Fixed typo in GitHub username used in changelog API call
- get_github_changelog() now correctly builds, sanitises, and caches the HTML response

**Changed**
- PHP 8.x modernisation: ?? operator replaces isset() ternaries throughout; return type declarations added to all non-trivial methods
- Frontend assets now only enqueue when at least one feature is enabled
- Admin JS stub removed; CSS-only toggle controls retained
- GitHub update check and changelog API calls are now transient-cached (6h and 12h respectively)
- Frontend scripts registered with defer strategy

**Removed**
- Removed stub frontend.js and admin.js files (contained only console.log statements)
- Removed changelog section from plugin settings page (replaced with GitHub-fetched content in update modal)

### Version 0.3
- Added disable hover effects on touch devices
- Added prevent horizontal scroll feature
- Added back to top button visibility controls
- Improved settings page UI with toggle switches and tooltips
- Added device-based menu display controls

### Version 0.2
- Added device-based menu display feature
- Improved settings page styling with feature sections
- Fixed orphan text logic
- Updated range for maximum hanging words (2-10)

### Version 0.1
- Initial release
- Orphan text fix functionality
- Basic settings page

For the complete changelog history, see [CHANGELOG.md](CHANGELOG.md).
