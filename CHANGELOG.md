# Changelog

All notable changes to Responsive Goodies are documented in this file.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)

## [0.4.0] - 2026-05-24

### Fixed
- Changelog modal now renders formatted HTML content from the GitHub Releases API (BUG-01)
- Fixed broken markdown-to-HTML ul-wrapping regex in convert_markdown_to_html()
- Fixed typo in GitHub username used in changelog API call
- get_github_changelog() now correctly builds, sanitises, and caches the HTML response

### Changed
- PHP 8.x modernisation: ?? operator replaces isset() ternaries throughout; return type declarations added to all non-trivial methods
- Frontend assets now only enqueue when at least one feature is enabled
- Admin JS stub removed; CSS-only toggle controls retained
- GitHub update check and changelog API calls are now transient-cached (6h and 12h respectively)
- Frontend scripts registered with defer strategy

### Removed
- Removed stub frontend.js and admin.js files (contained only console.log statements)
- Removed changelog section from plugin settings page (replaced with GitHub-fetched content in update modal)

## [0.3.8] - Previous release

See GitHub releases for earlier changelog entries.
