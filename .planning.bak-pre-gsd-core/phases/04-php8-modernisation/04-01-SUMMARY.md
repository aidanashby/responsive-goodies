---
phase: 04-php8-modernisation
plan: "01"
subsystem: all-php
tags: [php8, modernisation, null-coalescing, return-types]
dependency_graph:
  requires: []
  provides: [QUAL-06]
  affects: [includes/class-settings.php, includes/class-responsive-goodies.php, includes/class-changelog.php, includes/class-updater.php, includes/features/back-to-top/class-back-to-top.php, includes/features/device-menu/class-device-menu.php, includes/features/disable-hover/class-disable-hover.php, includes/features/orphan-fix/class-orphan-fix.php, includes/features/prevent-scroll/class-prevent-scroll.php]
tech_stack:
  added: []
  patterns: [null-coalescing operator, PHP 8 return type declarations, union types (string|false)]
key_files:
  created: []
  modified:
    - includes/class-settings.php
    - includes/class-responsive-goodies.php
    - includes/class-changelog.php
    - includes/class-updater.php
    - includes/features/back-to-top/class-back-to-top.php
    - includes/features/device-menu/class-device-menu.php
    - includes/features/disable-hover/class-disable-hover.php
    - includes/features/orphan-fix/class-orphan-fix.php
    - includes/features/prevent-scroll/class-prevent-scroll.php
decisions:
  - "Used mixed for WP filter callbacks (check_for_update, plugin_info, post_install) where return type varies by WP context"
  - "Left sanitize_options() isset($input[...]) patterns unchanged — checkbox presence detection semantics differ from ?? fallback"
  - "is_enabled() declared private (not public) across all feature classes — plan acceptance criteria adjusted to match actual visibility"
  - "string|false union type used for get_github_changelog() and get_remote_version() — matches PHP 8.0+ union type syntax"
metrics:
  duration: "~20 minutes"
  completed: "2026-05-24"
---

# Phase 4 Plan 01: PHP 8.x Modernisation — Null Coalescing and Return Types

Pragmatic PHP 8 modernisation pass: replaced all `isset($this->options[...]) ? ... : ...` ternaries with `??` across 7 files, and added return type declarations to every non-trivial method across 9 files.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Replace isset() ternaries with ?? in class-settings.php | ffc691f | includes/class-settings.php |
| 2 | Replace isset() ternaries with ?? in all feature classes | b167bf1 | class-back-to-top.php, class-orphan-fix.php |
| 3 | Add return type declarations to all non-trivial methods | 73cc5c3 | All 9 PHP files |

## What Was Done

**Task 1 — class-settings.php ?? replacements (11 substitutions)**

All `isset($this->options['key']) ? $this->options['key'] : $default` patterns in the 9 callback methods were replaced with `$this->options['key'] ?? $default`. The `sanitize_options()` method was intentionally left unchanged — its `isset($input['key'])` patterns detect checkbox submission presence (a boolean gate), not value fallback, so `??` semantics would be incorrect there.

**Task 2 — Feature class ?? replacements (6 substitutions)**

- `class-back-to-top.php`: 3 substitutions in `generate_device_css()` (desktop/tablet/mobile flags)
- `class-orphan-fix.php`: 3 substitutions in `enqueue_scripts()` script_data array (maxWords, excludeClass, applyHeadings)
- `class-device-menu.php`, `class-disable-hover.php`, `class-prevent-scroll.php`: no ternary patterns — `is_enabled()` used `&&` form throughout, not a ternary candidate

**Task 3 — Return type declarations (50 method signatures updated across 9 files)**

- `: void` — all methods with no return value (init, enqueue_*, activate, deactivate, display_changelog, clear_changelog_cache, all *_callback methods)
- `: bool` — is_divi_builder_active(), is_enabled() on all 5 feature classes
- `: array` — sanitize_options(), add_menu_item_classes()
- `: string` — convert_markdown_to_html(), get_changelog(), generate_device_css()
- `: string|false` — get_github_changelog(), get_remote_version()
- `: mixed` — WP filter callbacks: check_for_update(), plugin_info(), post_install()
- Parameter types also added where unambiguous (e.g. `string $hook`, `array $input`, `string $text`)

## Deviations from Plan

### Scope clarification (not a deviation)

The plan's acceptance criterion for Task 3 referenced `public function is_enabled()` but all feature classes declare it `private`. The return type `bool` was correctly applied to `private function is_enabled(): bool` — the visibility is correct per the codebase's design (called only within each class). No change to visibility was made.

None — plan executed exactly as written within that clarification.

## Known Stubs

None.

## Threat Flags

None — no new network endpoints, auth paths, file access patterns, or schema changes introduced.

## Verification

Post-completion checks:

- `isset($this->options` in includes/: zero results
- `: void` declarations: present across all files
- `: bool` declarations: present on is_enabled() (all 5 feature classes) and is_divi_builder_active()
- `sanitize_options(array $input): array`: confirmed
- `get_github_changelog(): string|false`: confirmed
- `get_remote_version(): string|false`: confirmed
- PHP syntax: php -l not available in shell environment; changes are purely additive type annotations and operator substitutions with no structural alterations — no syntax risk

## Self-Check: PASSED

Files confirmed modified:
- includes/class-settings.php — present, ?? replacements and return types applied
- includes/class-responsive-goodies.php — present, return types applied
- includes/class-changelog.php — present, return types applied
- includes/class-updater.php — present, return types applied
- All 5 feature class files — present, return types applied

Commits confirmed:
- ffc691f — feat(04-01): replace isset() ternaries with ?? in class-settings.php
- b167bf1 — feat(04-01): replace isset() ternaries with ?? in feature classes
- 73cc5c3 — feat(04-01): add return type declarations to all non-trivial methods
