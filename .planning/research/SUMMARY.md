# Research Summary — Responsive Goodies Quality Refactor

## Executive Summary

This is a bounded quality refactor of an existing, working WordPress plugin — not a new build. The plugin has a sound architecture (feature-flag orchestrator, self-contained feature directories, single options array) that must be preserved intact. The work is 11 discrete requirements: one bug fix, eight quality requirements, and two release tasks. The correct approach is a sequential pass in strict dependency order, treating naming consistency as the foundation everything else builds on.

The primary risk is data loss and breakage from seemingly safe changes. Renaming the top-level `responsive_goodies_options` key wipes live user settings. Renaming public `do_action`/`apply_filters` hooks silently breaks third-party customisations. Adding typed properties without checking all read paths causes fatal errors on PHP 8.x. Every change must be scoped strictly to what the requirement permits.

The bug fix (BUG-01 changelog modal) should be handled first as a standalone change — its transient-caching fix overlaps with QUAL-07 concerns and keeping it isolated prevents confusion during the quality pass.

---

## Stack

**PHP 8.x — apply:**
- Typed properties (prerequisite for `readonly`; sequence first within QUAL-06)
- Return type declarations (after typed properties)
- Match expressions — only for closed-set value mapping with strict comparison + single return value
- Nullsafe operator — for legitimately optional object chains (`get_current_screen()?->id`)
- `readonly` properties — constructor-assigned constants (version, option key)
- `??` null coalescing — replace `isset()` ternaries throughout

**PHP 8.x — do NOT apply:**
- Enums (structural change beyond quality pass)
- Fibers (irrelevant)
- Constructor promotion (feature class constructors call `get_option()`, not injected params)
- Named arguments in internal calls (noise without clarity benefit)

**WP 6.x — apply:**
- `wp_enqueue_script()` with `[ 'strategy' => 'defer' ]` for frontend scripts (WP 6.3+)
- Verify no `wp_add_inline_script` in `after` position before applying defer strategy
- `wp_admin_notice()` (WP 6.4+) — only if admin notices exist in codebase

**Tooling:**
- PHPCS with WordPress + WordPress-Docs + PHPCompatibility rulesets — install before Phase 2

---

## Recommended Phase Order

| Phase | Requirements | Rationale |
|-------|-------------|-----------|
| 1. Bug Fix | BUG-01 | Isolated; transient fix overlaps QUAL-07 — keep separate |
| 2. Naming Consistency | QUAL-01, 02, 03 | Foundation; lock names before types are added |
| 3. Dead Code + Capability | QUAL-04, 05 | Remove before annotating; decide `frontend.js` stub |
| 4. PHP 8.x Modernisation | QUAL-06 | Typed properties → return types → match/nullsafe/`??` |
| 5. Performance + Assets | QUAL-07 | Conditional enqueue, transient guard, defer strategy |
| 6. Docs + Release | QUAL-08, QUAL-09, REL-01, REL-02 | Final sweep; version bump last |

**Phase ordering rationale:**
- Naming before types prevents double-churn (rename after annotating = fix signatures twice)
- Dead code before types means less code to annotate
- Performance last because it touches hook registration, which should be stable by then
- QUAL-09 JS debug sweep in Phase 6 — across all JS files, not just `frontend.js`

---

## Critical Pitfalls

1. **Option key rename wipes live settings** — `responsive_goodies_options` is locked; scope QUAL-03 to internal array field names only
2. **Public hook rename breaks third-party customisations** — scope QUAL-02 to internal method names; never touch `do_action()`/`apply_filters()` call names
3. **Typed properties fatal if uninitialised** — every typed property needs a default or `?type`; trace all read paths before bulk-adding types
4. **No transient on update check** — `class-updater.php` fires two uncached GitHub API calls per cycle; add 6–12 hour transient guard in QUAL-07
5. **`upgrader_post_install` folder constant must not drift** — any change to folder-name constants in `class-updater.php` silently deactivates the plugin post-update

---

## Pre-Execution Questions (resolve during relevant phase)

| Question | Phase |
|----------|-------|
| Does `frontend.js` serve any purpose, or is it an empty stub? Determines whether the file is removed entirely | Phase 3 |
| Are there `wp_add_inline_script` calls in `after` position against enqueued handles? (silently strips defer strategy if so) | Phase 5 |
| Does `class-settings.php` output any `<div class="notice">` admin notices? (determines `wp_admin_notice()` migration) | Phase 5 |
| Does `class-updater.php` or `class-changelog.php` accept constructor parameters? (determines constructor promotion applicability) | Phase 4 |

---

## Confidence

**HIGH** — All stack recommendations verified against PHP.net and Make WordPress Core. All 11 requirements are fixed scope. Architecture guidance derived from direct codebase analysis. Pitfalls are concrete with specific prevention strategies.

No phases require additional pre-planning research. All patterns are standard with official documentation.
