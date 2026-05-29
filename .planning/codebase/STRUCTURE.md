# Codebase Structure

**Analysis Date:** 2026-05-14

## Directory Layout

```
responsive-goodies/
├── responsive-goodies.php          # Plugin entry point, constants, bootstrap
├── LICENSE
├── README.md
├── admin/
│   ├── css/
│   │   └── admin.css               # Settings page styles
│   └── js/
│       └── admin.js                # Settings page JavaScript
├── assets/
│   ├── css/
│   │   └── frontend.css            # Shared frontend styles (always enqueued)
│   └── js/
│       └── frontend.js             # Shared frontend JS (always enqueued, currently a stub)
└── includes/
    ├── class-responsive-goodies.php # Orchestrator — loads deps, runs features
    ├── class-settings.php           # Admin settings page and WP Settings API registration
    ├── class-updater.php            # GitHub-based self-update delivery
    ├── class-changelog.php          # GitHub Releases changelog fetcher
    └── features/
        ├── back-to-top/
        │   ├── class-back-to-top.php       # Feature class
        │   └── back-to-top.css             # Inline-extended via wp_add_inline_style
        ├── device-menu/
        │   ├── class-device-menu.php       # Feature class
        │   ├── device-menu.css             # Frontend hide-on-device media queries
        │   └── device-menu-admin.js        # Menu editor UI JS (nav-menus.php only)
        ├── disable-hover/
        │   ├── class-disable-hover.php     # Feature class
        │   └── disable-hover.css           # Touch-device hover suppression CSS
        ├── orphan-fix/
        │   ├── class-orphan-fix.php        # Feature class
        │   └── orphan-fix.js               # Client-side text manipulation
        └── prevent-scroll/
            ├── class-prevent-scroll.php    # Feature class
            └── prevent-scroll.css          # overflow-x suppression CSS
```

## Directory Purposes

**`/` (root):**
- Contains the plugin entry file and repo metadata only
- Key files: `responsive-goodies.php`

**`admin/`:**
- Assets scoped to wp-admin only — loaded exclusively on the plugin settings page (`settings_page_responsive-goodies` hook)
- Key files: `admin/css/admin.css`, `admin/js/admin.js`

**`assets/`:**
- Shared frontend assets enqueued on every frontend page load (unconditionally in `enqueue_frontend_assets()`)
- `frontend.js` is currently a stub (logs to console only)
- Key files: `assets/css/frontend.css`, `assets/js/frontend.js`

**`includes/`:**
- All PHP classes — orchestrator, settings, updater, changelog
- No sub-namespace separation; flat require_once chain

**`includes/features/`:**
- One subdirectory per feature, each self-contained with its PHP class and any CSS/JS it needs
- Feature directories are named with hyphens matching the class name convention
- Key files: one `class-{feature}.php` per directory, plus co-located assets

## Key File Locations

**Entry Point:**
- `responsive-goodies.php` — defines constants, requires classes, registers hooks

**Orchestrator:**
- `includes/class-responsive-goodies.php` — `Responsive_Goodies` class; `run()` method is the main execution path

**Settings:**
- `includes/class-settings.php` — all admin UI, field callbacks, sanitisation

**Self-Update:**
- `includes/class-updater.php` — GitHub API update check and post-install folder fix
- `includes/class-changelog.php` — GitHub API changelog fetch with transient cache

**Features:**
- `includes/features/back-to-top/class-back-to-top.php`
- `includes/features/device-menu/class-device-menu.php`
- `includes/features/disable-hover/class-disable-hover.php`
- `includes/features/orphan-fix/class-orphan-fix.php`
- `includes/features/prevent-scroll/class-prevent-scroll.php`

## Naming Conventions

**Files:**
- PHP classes: `class-{kebab-case-descriptor}.php` (e.g. `class-back-to-top.php`)
- CSS: `{kebab-case-feature}.css` (e.g. `disable-hover.css`)
- JS: `{kebab-case-feature}.js` or `{kebab-case-feature}-admin.js`

**Directories:**
- Feature directories: `{kebab-case-feature}/` matching the feature's CSS handle

**PHP Classes:**
- PascalCase with `Responsive_Goodies_` prefix (e.g. `Responsive_Goodies_Back_To_Top`)

**WordPress handles (enqueue slugs):**
- Pattern: `responsive-goodies-{feature}` (e.g. `responsive-goodies-back-to-top`)

**Option keys:**
- Single array: `responsive_goodies_options`
- Per-feature keys prefixed: `{feature_slug}_{setting}` (e.g. `orphan_fix_max_words`, `back_to_top_desktop`)

**Post meta keys:**
- Prefixed with `_rg_`: `_rg_show_desktop`, `_rg_show_tablet`, `_rg_show_mobile`

## Where to Add New Code

**New Feature:**
1. Create `includes/features/{feature-name}/` directory
2. Add `class-{feature-name}.php` implementing `__construct()`, `is_enabled()`, `init()` pattern
3. Add feature CSS/JS in same directory
4. Require the class in `includes/class-responsive-goodies.php` → `load_dependencies()`
5. Instantiate in `Responsive_Goodies::run()` → features array
6. Add option key(s) to default options array in `Responsive_Goodies::activate()`
7. Add settings field(s) in `includes/class-settings.php` → `init_settings()` and add sanitisation in `sanitize_options()`

**New Admin-Only Asset:**
- Add to `admin/css/` or `admin/js/`
- Enqueue in `Responsive_Goodies::enqueue_admin_assets()` with appropriate `$hook` guard

**New Shared Frontend Asset:**
- Add to `assets/css/` or `assets/js/`
- Enqueue in `Responsive_Goodies::enqueue_frontend_assets()`

**New Plugin-Level Class (not a feature):**
- Add to `includes/class-{name}.php`
- Require in `responsive-goodies.php` or in `load_dependencies()`

## Special Directories

**`.git/`:**
- Purpose: Git version control
- Generated: Yes
- Committed: No

**`.planning/`:**
- Purpose: GSD workflow planning documents
- Generated: By GSD tooling
- Committed: Yes (tracked in repo)

---

*Structure analysis: 2026-05-14*
