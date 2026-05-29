# Features Research — Responsive Goodies Refactor

## Coverage

All eleven requirement IDs from PROJECT.md with specific, testable acceptance criteria.

---

## BUG-01 — Changelog Modal Fix

**Acceptance criteria:**
- Changelog modal renders HTML content in wp-admin update notice (not empty / not a GitHub link fallback)
- `json_decode` result validated before body access; no PHP notice on malformed API response
- Fallback GitHub link still renders correctly on API failure
- `rg_github_changelog` transient written with explicit TTL (`12 * HOUR_IN_SECONDS`)

**Verification:** Trigger update check in wp-admin; open the "View changelog" link; confirm content renders. Also delete transient via WP CLI / Query Monitor and reopen to confirm refresh works.

**Dependencies:** Must be fixed before REL-01 (changelog entry documents the fix).

---

## QUAL-01 — Class and Method Naming Consistency

**Standard (WordPress):**
- Classes: `PascalCase` with `Responsive_Goodies_` prefix (e.g. `Responsive_Goodies_Back_To_Top`)
- Methods: `snake_case` (e.g. `is_enabled()`, `register_hooks()`)
- No mixing of camelCase methods and snake_case methods within the same class

**Acceptance criteria:**
- PHPCS WordPress ruleset reports zero class/method naming violations
- All five feature classes follow identical naming structure

**Verification:** `phpcs --standard=WordPress includes/`

**Dependencies:** Must complete before QUAL-02 (hook names derive from class naming conventions).

---

## QUAL-02 — Hook and Filter Naming

**Standard:**
- Action names: `responsive_goodies_{feature}_{event}` (e.g. `responsive_goodies_back_to_top_init`)
- Filter names: `responsive_goodies_{feature}_{property}` (e.g. `responsive_goodies_back_to_top_selector`)
- No bare `rg_` prefix hooks mixed with `responsive_goodies_` prefix hooks

**Acceptance criteria:**
- All `add_action()`, `add_filter()`, `do_action()`, `apply_filters()` calls in the plugin use consistent `responsive_goodies_` prefix
- No public hook names are changed (breaking change — see PITFALLS.md)
- Internal hook registrations are consistent

**Anti-feature:** Do NOT rename any public `do_action()` / `apply_filters()` the plugin exposes to third parties — this is a breaking change.

**Verification:** `grep -r "add_action\|add_filter\|do_action\|apply_filters" includes/`

---

## QUAL-03 — Option and Settings Key Naming

**Standard:**
- Top-level `wp_options` key: `responsive_goodies_options` — **MUST NOT change** (would wipe live settings)
- Array field names within the options array: consistent `snake_case` matching feature slugs
- Settings API field IDs: consistent with array key names

**Acceptance criteria:**
- All `get_option()` / `update_option()` calls use `responsive_goodies_options` unchanged
- Internal array keys within the options object follow consistent naming
- Settings API field `id` attributes match internal array keys

**Anti-feature:** Never rename the top-level `wp_options` key. Any rename requires a migration function with version flag guard.

**Verification:** `grep -r "get_option\|update_option" includes/`

---

## QUAL-04 — Capability Checks

**Standard:**
- `current_user_can( 'manage_options' )` before any privileged operation
- Must fire at `init` or later (after `wp_set_current_user()` has run)
- Settings API enforces capability for the settings page automatically — no manual check needed there
- Any AJAX handler (`wp_ajax_responsive_goodies_*`) needs its own explicit check at handler entry

**Acceptance criteria:**
- Every `wp_ajax_*` handler starts with `current_user_can()` check and `wp_die()` on failure
- No privileged option write runs without capability verification
- Settings page callback does not duplicate the check (Settings API covers it)

**Verification:** Grep for `wp_ajax_` hooks; verify each handler has `current_user_can` at top.

---

## QUAL-05 — Dead Code Removal

**Acceptance criteria:**
- No unrooted methods (methods never called from within the plugin or hooked to WordPress)
- No commented-out code blocks (use git history instead)
- No unreachable branches (conditions that can never be true given PHP 8.x / WP 6.x minimum)
- No `var_dump()`, `print_r()`, `error_log()` debug calls in production code

**Caution:** Empty deactivation hook body — do NOT silently remove. Either add cleanup logic or add a comment explaining the placeholder is intentional. See PITFALLS.md #7.

**Verification:** PHPCS `WordPress.PHP.DevelopmentFunctions` sniff; manual review of unreachable branches.

---

## QUAL-06 — PHP 8.x Modernisation

**Apply:**
- **Typed properties** — where type is always known at construction time; use `?type` for nullable; never leave a typed property without a default or assignment in constructor
- **Match expression** — only where strict comparison + single return value applies; do not replace switch when fall-through is intentional
- **Nullsafe operator** — for legitimately optional object chains (e.g. `get_current_screen()?->id`); not as a mask for logic errors
- **`??` null coalescing** — replace `isset()` ternaries throughout
- **`readonly` properties** — constructor-assigned constants (version string, option key); not on anything WordPress hooks write post-construction

**Do NOT apply:**
- Enums — structural change beyond a quality pass
- Fibers — irrelevant to synchronous WP execution
- Constructor promotion — feature class constructors call `get_option()`, not injected params; not applicable
- Named arguments in internal calls — adds noise without clarity benefit at this scale

**Build order within QUAL-06:**
1. Typed properties first (prerequisite for readonly)
2. Return type declarations
3. Match / null-safe / `??` consistency

**Acceptance criteria:**
- `php -l` passes on all changed files
- PHP 8.4 implicit nullable pattern (`string $x = null`) not introduced (use `?string $x = null`)
- No `TypeError` fatals on uninitialised typed properties

---

## QUAL-07 — Performance / Asset Loading

**Acceptance criteria:**
- Frontend JS/CSS only enqueued when at least one feature is enabled (check `is_enabled()` before enqueue)
- Admin assets scoped to the plugin settings page hook (not all admin pages)
- Update check wrapped in transient guard (6–12 hour TTL) to prevent double GitHub API calls
- `get_option()` called at most once per request per key

**WP 6.3 asset strategy:** Use `[ 'strategy' => 'defer' ]` in `wp_enqueue_script()` for frontend scripts. Verify no `wp_add_inline_script` in `after` position against the same handle (inline after-scripts silently strip the strategy).

**Acceptance criteria:** Query Monitor shows no duplicate `get_option` calls; network tab shows no asset requests when all features disabled.

---

## QUAL-08 — PHPDoc Gaps

**Scope:** Complex or non-obvious methods only. Skip trivial getters, one-liners, and anything self-evident from the method signature.

**Standard (WordPress):**
- Third-person singular summary line (`Initialises feature hooks if feature is enabled.`)
- `@param type $name Description.` for all non-obvious parameters
- `@return type Description.` for all non-void returns
- `@since 0.4.0` on newly documented methods
- Do NOT use `@package WordPress`

**Acceptance criteria:**
- No PHPDoc blocks on trivially obvious methods (adding noise)
- All genuinely complex logic (GitHub API parsing, Divi builder detection, changelog HTML construction) has a summary and param/return docs

---

## QUAL-09 — Debug Statement Removal

**Acceptance criteria:**
- Zero `console.log` in any shipped JS file (all files, not just `frontend.js`)
- `console.error` / `console.warn` acceptable only if intentional and accompanied by a comment
- No `var_dump`, `print_r`, `error_log` in PHP (covered by QUAL-05)

**Verification:** `grep -r "console\.log" assets/js/`

---

## REL-01 — CHANGELOG.md

**Format:** [Keep a Changelog](https://keepachangelog.com) — new entry at top under `## [0.4.0] - YYYY-MM-DD`

**Acceptance criteria:**
- CHANGELOG.md exists in the plugin root
- Entry covers: Bug Fixes (BUG-01), Changed (naming consistency, PHP 8.x modernisation), Removed (dead code, debug statements)
- No entry references internal implementation details users don't care about

**Dependencies:** BUG-01 must be documented; REL-01 must complete before REL-02.

---

## REL-02 — Version Bump

**Acceptance criteria:**
- Version string `0.4.0` in: plugin header (`Version:` field), `RESPONSIVE_GOODIES_VERSION` constant, CHANGELOG.md entry header
- `grep -r "0\.3\.8" .` returns zero results in the plugin directory
- No version string in `upgrader_post_install` folder constant changed (see PITFALLS.md #2)

---

## Dependency Chain

```
QUAL-01 → QUAL-02  (hook names derive from class naming)
QUAL-03 → QUAL-04  (capability checks reference correct option keys)
QUAL-06 → QUAL-05  (modernisation removes compat shims as dead code)
BUG-01  → REL-01   (fix must be documented)
REL-01  → REL-02   (changelog version must match bumped constant)
```

## Verification Toolchain

| Check | Tool |
|-------|------|
| QUAL-01, 02, 03, 04, 05, 08, 09 | `phpcs --standard=WordPress` |
| QUAL-06 PHP 8.4 nullable | `php -l` on each changed file |
| QUAL-07 duplicate options | Query Monitor |
| QUAL-07 assets | Browser network tab with all features disabled |
| QUAL-09 JS | `grep -r "console\.log" assets/js/` |
| REL-02 version string | `grep -r "0\.3\.8" .` returns zero |
| BUG-01 modal | Manual wp-admin test |
