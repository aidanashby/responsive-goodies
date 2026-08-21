---
plan: 01-01
phase: 01-bug-fix
status: complete
completed: 2026-05-14
commit: 96039bc
---

## Summary

Fixed the two defects in `includes/class-changelog.php` that caused the settings-page changelog modal to always render blank.

## Tasks

| # | Task | Status |
|---|------|--------|
| 1 | Fix `convert_markdown_to_html()` ul-wrapping regex | ✓ Complete |
| 2 | Complete `get_github_changelog()` — build HTML and set transient | ✓ Complete |

## What Was Built

**Task 1 — Regex fix (line 86):** Replaced `/(<li>.*<\/li>)/s` with `/(?:<li>.+<\/li>\n?)+/`. The `/s` flag caused the wildcard to span newlines, collapsing all `<li>` elements across the entire body into a single `<ul>`. The new pattern matches one or more consecutive `<li>` lines as a block, producing a separate `<ul>` per list section.

**Task 2 — Method completion (lines 58–75):** Replaced the stub comment with:
- `array_slice( $releases, 0, 3 )` — caps output to last 3 releases
- foreach loop building `<div class="rg-changelog-release">` blocks with `<h3>` tag name and converted body
- `wp_kses_post()` applied to full HTML before caching (T-01-03 mitigation)
- `set_transient( 'rg_github_changelog', $html, 12 * HOUR_IN_SECONDS )` — 12-hour cache
- `return $html` inside the try block; existing catch returns false on any exception

## Key Files

### Modified
- `includes/class-changelog.php` — complete `get_github_changelog()` and fixed `convert_markdown_to_html()`

## Acceptance Checks

| Check | Result |
|-------|--------|
| `set_transient` count = 1 | ✓ |
| Stub comment absent | ✓ |
| `HOUR_IN_SECONDS` present | ✓ |
| `rg-changelog-release` wrapper present | ✓ |
| `array_slice` present | ✓ |
| `wp_kses_post` present | ✓ |
| Line 86 regex has no `/s` flag | ✓ |
| `php -l` | ⚠ PHP not on PATH — manual check required |

## Deviations

None. Implementation follows the plan exactly.

## Manual Testing Required

1. `php -l includes/class-changelog.php` — confirm no syntax errors (PHP not available in shell; run via Local or WP-CLI).
2. Load WordPress admin → Settings > Responsive Goodies → open changelog modal. Confirm HTML content from the last 3 GitHub releases is visible.
3. Delete the `rg_github_changelog` transient (WP-CLI: `wp transient delete rg_github_changelog` or via a cache flush), reload, and confirm it re-fetches and re-caches.

## Self-Check: PASSED
