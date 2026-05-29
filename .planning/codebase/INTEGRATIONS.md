# External Integrations

**Analysis Date:** 2026-05-14

## APIs & External Services

**GitHub Releases API:**
- Service: GitHub REST API (`https://api.github.com/repos/aidnashby/responsive-goodies/releases`)
- Used for: plugin self-update checks and changelog display in wp-admin
- Client: `wp_remote_get()` — WordPress HTTP API, no SDK
- Auth: none (public repo, unauthenticated requests)
- Files: `includes/class-updater.php`, `includes/class-changelog.php`
- Timeout: 10 seconds (`class-changelog.php`); no explicit timeout in `class-updater.php`
- Caching: `get_transient('rg_github_changelog')` used for changelog; no transient caching on update check

**GitHub Download (zip packages):**
- Used for: delivering plugin update packages via WordPress updater
- URL pattern: `https://github.com/aidnashby/responsive-goodies/archive/refs/tags/v{version}.zip`
- Triggered by: WordPress core upgrader after update check confirms newer version

## Data Storage

**Databases:**
- WordPress options table — single option key `responsive_goodies_options` stores all plugin settings as a serialised PHP array
- WordPress post meta — device visibility per nav menu item stored as `_rg_show_desktop`, `_rg_show_tablet`, `_rg_show_mobile` on nav menu item post objects
- WordPress transients — `rg_github_changelog` caches GitHub changelog response (TTL not set in current code — see Concerns)

**File Storage:**
- Local filesystem only (plugin assets)

**Caching:**
- WordPress transient API used for changelog (`rg_github_changelog`)
- No transient caching on update version checks

## Authentication & Identity

**Auth Provider:**
- WordPress built-in capability checks: `manage_options` required to access settings page
- No custom auth logic

## Monitoring & Observability

**Error Tracking:**
- None

**Logs:**
- `console.log('Responsive Goodies frontend scripts loaded')` in `assets/js/frontend.js` — debug only, no structured logging

## CI/CD & Deployment

**Hosting:**
- Krystal shared hosting (production)
- Updates distributed via GitHub Releases; WordPress core updater handles installation

**CI Pipeline:**
- None detected — no GitHub Actions, no test runner config

**Version control:**
- Git repository: `https://github.com/aidnashby/responsive-goodies` (inferred from updater constants)
- Remote branch: `origin/main`

## Webhooks & Callbacks

**Incoming:**
- None

**Outgoing:**
- None (update checks are pull-based, not push-based)

## WordPress Hook Surface

**Actions registered:**
- `plugins_loaded` — initialises main plugin class
- `admin_menu` — registers settings page under Settings
- `admin_init` — registers settings fields and sections
- `admin_enqueue_scripts` — enqueues admin CSS/JS on settings page and nav-menus.php
- `wp_enqueue_scripts` — enqueues frontend CSS/JS per enabled feature
- `wp_nav_menu_item_custom_fields` — adds device visibility checkboxes to menu item editor
- `wp_update_nav_menu_item` — saves device visibility post meta

**Filters registered:**
- `plugin_action_links_{plugin_basename}` — adds Settings link on Plugins page
- `pre_set_site_transient_update_plugins` — injects GitHub update data into WordPress update check
- `plugins_api` — provides plugin info to WordPress updater modal
- `upgrader_post_install` — corrects folder name after GitHub zip extraction
- `nav_menu_css_class` — appends `rg-hide-desktop/tablet/mobile` classes to menu items

**Activation/Deactivation hooks:**
- `register_activation_hook` — sets default options, clears changelog transient
- `register_deactivation_hook` — registered but body is empty

---

*Integration audit: 2026-05-14*
