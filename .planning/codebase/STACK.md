# Technology Stack

**Analysis Date:** 2026-05-14

## Languages

**Primary:**
- PHP — All server-side plugin logic, WordPress hooks, settings registration, feature classes
- JavaScript (ES5/jQuery) — Frontend feature behaviour (orphan fix, admin menu UI)
- CSS — Feature-specific and shared frontend/admin styles

## Runtime

**Environment:**
- WordPress (requires 5.0+, tested to 6.4)
- PHP (version not pinned; WordPress minimum implies PHP 7.4+ in practice)

**Package Manager:**
- None — no Composer, no npm, no lockfile
- Dependencies are WordPress core APIs only

## Frameworks

**Core:**
- WordPress Plugin API — hooks, filters, options, wp_remote_get, wp_enqueue_scripts
- No PHP framework (no Laravel, Symfony, etc.)

**Frontend:**
- jQuery — bundled with WordPress; used as dependency for `frontend.js`, `orphan-fix.js`, `device-menu-admin.js`
- No CSS preprocessor (plain CSS throughout)

**Build/Dev:**
- None detected — no webpack, gulp, Vite, or build pipeline
- All assets are hand-authored and committed directly

## Key Dependencies

**Critical:**
- WordPress core — all functionality depends on WP APIs (options, post meta, enqueue, remote HTTP)
- jQuery — required by frontend and admin JS; declared as dependency via `wp_enqueue_script`

**Infrastructure:**
- GitHub Releases API (`https://api.github.com/repos/aidnashby/responsive-goodies/releases`) — used for self-hosted update delivery and changelog retrieval via `wp_remote_get`

## Configuration

**Environment:**
- Plugin settings stored as a single serialised array in `wp_options` under key `responsive_goodies_options`
- No `.env` file; no external config file
- GitHub username and repo name hardcoded in `class-updater.php` and `class-changelog.php`

**Build:**
- No build config files

## Platform Requirements

**Development:**
- WordPress local install (project uses `C:\Users\aidan\Studio\divi-5` per global config)
- PHP with `php -l` available for syntax checking

**Production:**
- Shared hosting (Krystal) running WordPress + Divi 5
- Divi-specific compatibility logic present: features are suppressed when Divi front-end builder is active (`et_fb=1` GET param or `et_fb_is_enabled()`)
- Back-to-top feature targets Divi's `.et_pb_scroll_top` CSS selector directly

---

*Stack analysis: 2026-05-14*
