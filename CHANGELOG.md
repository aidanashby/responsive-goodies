# Changelog

All notable changes to Responsive Goodies are documented in this file.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)

## [0.5.2] - 2026-09-28

### Changed
- Updates now come through the bundled Plugin Update Checker library instead of the plugin's own updater. The automatic updates toggle on the Plugins screen now works, and updates install into the right folder without a manual move.
- Update checks now also run in the background (WP-Cron), so automatic updates find new versions.

### Added
- Plugin icon on the Plugins and Updates screens.
- `Update URI` header, so WordPress.org can't offer an unrelated plugin with the same name as an update.

### Fixed
- Uninstall now also removes the update checker's stored data.

## [0.5.1] - 2026-09-28

### Fixed
- Device Menu Controls: new menu items now default to visible on desktop and hidden on tablet/mobile, as intended, instead of visible everywhere. WordPress writes menu-item postmeta immediately on item creation (before the visibility checkboxes are ever shown), which was overwriting all three device flags to "hidden" before the user touched anything; the save handler now only writes those flags when the checkboxes are actually present in the request.

## [0.5.0] - 2026-07-20

### Added
- Mobile Hamburger Control: a per-menu "Show hamburger menu on mobile" checkbox in the Menu Settings box (Appearance → Menus), checked by default. When unticked, that menu's Divi module keeps its items visible on mobile instead of collapsing to a hamburger. Gated by a master toggle on the settings page (default off).
- uninstall.php: full cleanup on plugin deletion — removes the options row, cached transients, and all plugin post/term meta (closes a pre-existing data leak).

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
