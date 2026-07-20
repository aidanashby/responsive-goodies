# Architecture

**Analysis Date:** 2026-05-14

## Pattern Overview

**Overall:** Feature-flag plugin with a central orchestrator class and self-contained per-feature classes.

**Key Characteristics:**
- Single options array in `wp_options` controls all feature toggles
- Each feature is a separate class that self-registers its hooks inside an `init()` method — only called when enabled
- No autoloading; all files required explicitly in `class-responsive-goodies.php`
- Divi builder compatibility: the orchestrator suppresses all feature initialisation when the Divi front-end editor is active

## Layers

**Entry Point:**
- Purpose: Bootstrap constants, require core classes, wire activation hooks, initialise on `plugins_loaded`
- Location: `responsive-goodies.php`
- Contains: Constants (`RESPONSIVE_GOODIES_VERSION`, `RESPONSIVE_GOODIES_PLUGIN_DIR`, `RESPONSIVE_GOODIES_PLUGIN_URL`), top-level `add_action` and `add_filter` calls
- Depends on: WordPress core
- Used by: WordPress plugin loader

**Orchestrator:**
- Purpose: Load all dependencies, instantiate feature classes, enqueue shared assets, check Divi builder state
- Location: `includes/class-responsive-goodies.php`
- Contains: `Responsive_Goodies` class with `run()`, `load_dependencies()`, `is_divi_builder_active()`, `enqueue_frontend_assets()`, `enqueue_admin_assets()`
- Depends on: All feature classes, `Responsive_Goodies_Settings`
- Used by: Entry point via `responsive_goodies_init()`

**Settings:**
- Purpose: Admin UI — registers WordPress Settings API sections and fields, renders the settings page, sanitises input
- Location: `includes/class-settings.php`
- Contains: `Responsive_Goodies_Settings` class
- Depends on: WordPress Settings API, WordPress Options API
- Used by: Orchestrator (always initialised, even when Divi builder is active)

**Feature Classes:**
- Purpose: Each encapsulates one responsive utility — reads its own option flag, registers only its own hooks when enabled
- Location: `includes/features/{feature-name}/class-{feature-name}.php`
- Contains: One class per feature with `__construct()` (reads options), `init()` (registers hooks if enabled), `is_enabled()` (option check)
- Depends on: WordPress hooks, WordPress Options API, own CSS/JS assets
- Used by: Orchestrator

**Infrastructure Classes:**
- Purpose: Self-update delivery and changelog display via GitHub Releases API
- Location: `includes/class-updater.php`, `includes/class-changelog.php`
- Contains: `Responsive_Goodies_Updater`, `Responsive_Goodies_Changelog`
- Depends on: `wp_remote_get`, WordPress transient API, WordPress updater filters
- Used by: Entry point (updater) and Settings (changelog callback)

## Data Flow

**Settings Save:**
1. User submits form on `options-general.php?page=responsive-goodies`
2. WordPress Settings API calls `Responsive_Goodies_Settings::sanitize_options()`
3. Sanitised array saved to `wp_options` as `responsive_goodies_options`

**Feature Initialisation (page request):**
1. `plugins_loaded` fires → `responsive_goodies_init()` called
2. `Responsive_Goodies::run()` reads settings, checks Divi builder state
3. If not in Divi builder: instantiates all feature classes, calls `init()` on each
4. Each feature calls `get_option('responsive_goodies_options')` in its constructor and checks its own flag in `is_enabled()`
5. Enabled features register `wp_enqueue_scripts` (and other hooks where needed)

**Plugin Self-Update:**
1. WordPress fires `pre_set_site_transient_update_plugins`
2. `Responsive_Goodies_Updater::check_for_update()` calls GitHub Releases API via `wp_remote_get`
3. If remote version is higher, injects update object into transient
4. WordPress core handles download from GitHub zip URL
5. `upgrader_post_install` corrects extracted folder name to match expected plugin slug

**Device Menu (runtime):**
1. Nav menu item editor loads → `wp_nav_menu_item_custom_fields` renders device checkboxes
2. Save → `wp_update_nav_menu_item` writes `_rg_show_desktop/tablet/mobile` post meta
3. Frontend render → `nav_menu_css_class` filter appends `rg-hide-{device}` classes
4. Feature CSS hides elements at appropriate breakpoints via media queries

**Mobile Hamburger (runtime):**
1. Menu editor loads → `admin_footer-nav-menus.php` injects a "Show hamburger menu on mobile" checkbox into the Menu Settings box (checked by default)
2. Save → `wp_update_nav_menu` writes `_rg_show_mobile_hamburger` term meta (nonce + `edit_theme_options` guarded)
3. Frontend render → `wp_nav_menu_args` filter appends the `rg-nmh` marker class to the menu `<ul>` when the toggle is off
4. `mobile-hamburger-frontend.js` finds each `.rg-nmh` menu and adds `no-mobile-hamburger` to its Divi module wrapper (`.et_pb_menu` / `.et_pb_fullwidth_menu`)
5. Feature CSS keeps menu items visible and hides the hamburger at ≤980px

**Plugin Uninstall:**
1. Plugin deleted → WordPress runs `uninstall.php` (guarded by `WP_UNINSTALL_PLUGIN`)
2. Removes `responsive_goodies_options`, the `rg_*` transients, the device-menu post meta, and the mobile-hamburger term meta

## Key Abstractions

**Feature Class Contract:**
- Every feature implements: `__construct()` reading options, `is_enabled()` checking one option key, `init()` registering hooks only when enabled
- Examples: `includes/features/back-to-top/class-back-to-top.php`, `includes/features/device-menu/class-device-menu.php`
- Pattern: Guard-clause pattern — `init()` returns early if `!is_enabled()`

**Options Array:**
- Single flat array stored under `responsive_goodies_options`
- All feature settings namespaced by feature prefix (e.g. `orphan_fix_*`, `back_to_top_*`)
- Read fresh from `get_option()` in each class constructor — no shared state object

## Entry Points

**Plugin Bootstrap:**
- Location: `responsive-goodies.php`
- Triggers: WordPress loads all active plugins
- Responsibilities: Define constants, require classes, register activation/deactivation hooks, add `plugins_loaded` action

**Admin Settings Page:**
- Location: `includes/class-settings.php` → `settings_page()`
- Triggers: User visits `options-general.php?page=responsive-goodies`
- Responsibilities: Render all feature toggle fields, save via WordPress Settings API

## Error Handling

**Strategy:** Passive — errors are absorbed silently or degrade gracefully.

**Patterns:**
- `wp_remote_get` results checked with `is_wp_error()` before use; returns `false` on failure
- HTTP response code checked in `class-changelog.php` (200 required)
- `json_decode` result validated with `json_last_error()` check in changelog class
- Changelog display falls back to a GitHub link if API call fails
- No exceptions thrown; no custom error logging

## Cross-Cutting Concerns

**Logging:** None — no console.log in shipped assets, no PHP logging
**Validation:** Input sanitised via `sanitize_text_field()`, `absint()`, `isset()` checks in `sanitize_options()`
**Authentication:** `manage_options` capability check on settings page registration; no nonce on device menu meta save (`wp_update_nav_menu_item` is called by WordPress core which handles nonce); mobile-hamburger save verifies its own nonce and `edit_theme_options` capability
**Divi Compatibility:** `is_divi_builder_active()` in orchestrator suppresses all feature hooks during Divi visual editing; back-to-top feature targets `.et_pb_scroll_top` Divi selector directly

---

*Architecture analysis: 2026-05-14*
