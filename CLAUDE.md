<!-- GSD:project-start source:PROJECT.md -->
## Project

**Responsive Goodies — Quality Refactor**

A planned quality pass on the Responsive Goodies WordPress plugin (v0.3.8). No new features — the goal is to leave the codebase cleaner, more consistent, more secure, and free of known bugs, with a version bump and changelog entry to mark the work.

**Core Value:** Every feature works correctly and the codebase is maintainable — naming is consistent, capability checks are in place, dead code is gone, obvious documentation gaps are filled, and the confirmed changelog bug is fixed.
<!-- GSD:project-end -->

<!-- GSD:stack-start source:codebase/STACK.md -->
## Technology Stack

## Languages
- PHP — All server-side plugin logic, WordPress hooks, settings registration, feature classes
- JavaScript (ES5/jQuery) — Frontend feature behaviour (orphan fix, admin menu UI)
- CSS — Feature-specific and shared frontend/admin styles
## Runtime
- WordPress (requires 5.0+, tested to 6.4)
- PHP (version not pinned; WordPress minimum implies PHP 7.4+ in practice)
- None — no Composer, no npm, no lockfile
- Dependencies are WordPress core APIs only
## Frameworks
- WordPress Plugin API — hooks, filters, options, wp_remote_get, wp_enqueue_scripts
- No PHP framework (no Laravel, Symfony, etc.)
- jQuery — bundled with WordPress; used as dependency for `frontend.js`, `orphan-fix.js`, `device-menu-admin.js`
- No CSS preprocessor (plain CSS throughout)
- None detected — no webpack, gulp, Vite, or build pipeline
- All assets are hand-authored and committed directly
## Key Dependencies
- WordPress core — all functionality depends on WP APIs (options, post meta, enqueue, remote HTTP)
- jQuery — required by frontend and admin JS; declared as dependency via `wp_enqueue_script`
- GitHub Releases API (`https://api.github.com/repos/aidnashby/responsive-goodies/releases`) — used for self-hosted update delivery and changelog retrieval via `wp_remote_get`
## Configuration
- Plugin settings stored as a single serialised array in `wp_options` under key `responsive_goodies_options`
- No `.env` file; no external config file
- GitHub username and repo name hardcoded in `class-updater.php` and `class-changelog.php`
- No build config files
## Platform Requirements
- WordPress local install (project uses `C:\Users\aidan\Studio\divi-5` per global config)
- PHP with `php -l` available for syntax checking
- Shared hosting (Krystal) running WordPress + Divi 5
- Divi-specific compatibility logic present: features are suppressed when Divi front-end builder is active (`et_fb=1` GET param or `et_fb_is_enabled()`)
- Back-to-top feature targets Divi's `.et_pb_scroll_top` CSS selector directly
<!-- GSD:stack-end -->

<!-- GSD:conventions-start source:CONVENTIONS.md -->
## Conventions

Conventions not yet established. Will populate as patterns emerge during development.
<!-- GSD:conventions-end -->

<!-- GSD:architecture-start source:ARCHITECTURE.md -->
## Architecture

## Pattern Overview
- Single options array in `wp_options` controls all feature toggles
- Each feature is a separate class that self-registers its hooks inside an `init()` method — only called when enabled
- No autoloading; all files required explicitly in `class-responsive-goodies.php`
- Divi builder compatibility: the orchestrator suppresses all feature initialisation when the Divi front-end editor is active
## Layers
- Purpose: Bootstrap constants, require core classes, wire activation hooks, initialise on `plugins_loaded`
- Location: `responsive-goodies.php`
- Contains: Constants (`RESPONSIVE_GOODIES_VERSION`, `RESPONSIVE_GOODIES_PLUGIN_DIR`, `RESPONSIVE_GOODIES_PLUGIN_URL`), top-level `add_action` and `add_filter` calls
- Depends on: WordPress core
- Used by: WordPress plugin loader
- Purpose: Load all dependencies, instantiate feature classes, enqueue shared assets, check Divi builder state
- Location: `includes/class-responsive-goodies.php`
- Contains: `Responsive_Goodies` class with `run()`, `load_dependencies()`, `is_divi_builder_active()`, `enqueue_frontend_assets()`, `enqueue_admin_assets()`
- Depends on: All feature classes, `Responsive_Goodies_Settings`
- Used by: Entry point via `responsive_goodies_init()`
- Purpose: Admin UI — registers WordPress Settings API sections and fields, renders the settings page, sanitises input
- Location: `includes/class-settings.php`
- Contains: `Responsive_Goodies_Settings` class
- Depends on: WordPress Settings API, WordPress Options API
- Used by: Orchestrator (always initialised, even when Divi builder is active)
- Purpose: Each encapsulates one responsive utility — reads its own option flag, registers only its own hooks when enabled
- Location: `includes/features/{feature-name}/class-{feature-name}.php`
- Contains: One class per feature with `__construct()` (reads options), `init()` (registers hooks if enabled), `is_enabled()` (option check)
- Depends on: WordPress hooks, WordPress Options API, own CSS/JS assets
- Used by: Orchestrator
- Purpose: Self-update delivery and changelog display via GitHub Releases API
- Location: `includes/class-updater.php`, `includes/class-changelog.php`
- Contains: `Responsive_Goodies_Updater`, `Responsive_Goodies_Changelog`
- Depends on: `wp_remote_get`, WordPress transient API, WordPress updater filters
- Used by: Entry point (updater) and Settings (changelog callback)
## Data Flow
## Key Abstractions
- Every feature implements: `__construct()` reading options, `is_enabled()` checking one option key, `init()` registering hooks only when enabled
- Examples: `includes/features/back-to-top/class-back-to-top.php`, `includes/features/device-menu/class-device-menu.php`
- Pattern: Guard-clause pattern — `init()` returns early if `!is_enabled()`
- Single flat array stored under `responsive_goodies_options`
- All feature settings namespaced by feature prefix (e.g. `orphan_fix_*`, `back_to_top_*`)
- Read fresh from `get_option()` in each class constructor — no shared state object
## Entry Points
- Location: `responsive-goodies.php`
- Triggers: WordPress loads all active plugins
- Responsibilities: Define constants, require classes, register activation/deactivation hooks, add `plugins_loaded` action
- Location: `includes/class-settings.php` → `settings_page()`
- Triggers: User visits `options-general.php?page=responsive-goodies`
- Responsibilities: Render all feature toggle fields, save via WordPress Settings API
## Error Handling
- `wp_remote_get` results checked with `is_wp_error()` before use; returns `false` on failure
- HTTP response code checked in `class-changelog.php` (200 required)
- `json_decode` result validated with `json_last_error()` check in changelog class
- Changelog display falls back to a GitHub link if API call fails
- No exceptions thrown; no custom error logging
## Cross-Cutting Concerns
<!-- GSD:architecture-end -->

<!-- GSD:skills-start source:skills/ -->
## Project Skills

No project skills found. Add skills to any of: `.claude/skills/`, `.agents/skills/`, `.cursor/skills/`, `.github/skills/`, or `.codex/skills/` with a `SKILL.md` index file.
<!-- GSD:skills-end -->

<!-- GSD:workflow-start source:GSD defaults -->
## GSD Workflow Enforcement

Before using Edit, Write, or other file-changing tools, start work through a GSD command so planning artifacts and execution context stay in sync.

Use these entry points:
- `/gsd-quick` for small fixes, doc updates, and ad-hoc tasks
- `/gsd-debug` for investigation and bug fixing
- `/gsd-execute-phase` for planned phase work

Do not make direct repo edits outside a GSD workflow unless the user explicitly asks to bypass it.
<!-- GSD:workflow-end -->



<!-- GSD:profile-start -->
## Developer Profile

> Profile not yet configured. Run `/gsd-profile-user` to generate your developer profile.
> This section is managed by `generate-claude-profile` -- do not edit manually.
<!-- GSD:profile-end -->
