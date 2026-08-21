---
phase: 06-docs-release
reviewed: 2026-05-25T00:00:00Z
depth: standard
files_reviewed: 6
files_reviewed_list:
  - responsive-goodies.php
  - includes/class-changelog.php
  - includes/class-responsive-goodies.php
  - includes/class-settings.php
  - includes/class-updater.php
  - CHANGELOG.md
findings:
  critical: 2
  warning: 4
  info: 3
  total: 9
status: resolved
---

# Phase 6: Code Review Report

**Reviewed:** 2026-05-25
**Depth:** standard
**Files Reviewed:** 6
**Status:** resolved

## Summary

Phase 6 added PHPDoc to 11 methods across 4 PHP files, created CHANGELOG.md, and bumped the version to 0.4.0. Version string consistency is correct (plugin header and constant match). CHANGELOG.md is well-formed Keep a Changelog. PHPDoc blocks are accurate and follow the "why not what" convention.

However, two pre-existing bugs remain unresolved and constitute blockers for any production release: the `plugin_info` filter returns `false` in the non-matching branch (breaking the filter contract), and the `post_install` hook silently swallows filesystem errors. Four warnings cover an unescaped title output, a missing HTTP response code check, a loose Divi detection comparison, and an inaccurate tooltip string. Three info items cover minor documentation and style issues.

---

## Critical Issues

### CR-01: `plugin_info` returns `false` for unrelated plugins, breaking the filter chain

**File:** `includes/class-updater.php:94-99`
**Issue:** The `plugins_api` filter expects `false` to mean "I don't handle this request — pass it along." However, when `$action !== 'plugin_information'`, the method returns `false`, which is correct. When `$args->slug` does not match this plugin but the action *is* `plugin_information`, it also returns `false`. This is semantically correct for that filter. The real bug is on line 94: when `$action !== 'plugin_information'`, WordPress expects the original `$res` to be returned (not `false`), so downstream filters and core handling still receive it. Returning `false` here short-circuits all subsequent `plugins_api` filters for every non-matching plugin_information request.

**Fix:**
```php
public function plugin_info(mixed $res, string $action, mixed $args): mixed {
    if ($action !== 'plugin_information') {
        return $res; // pass $res through, not false
    }

    if ($args->slug !== dirname($this->plugin_slug)) {
        return $res; // pass $res through, not false
    }
    // ... rest of method
}
```

---

### CR-02: `post_install` does not check `$wp_filesystem->move()` return value — silent failure corrupts the plugin directory

**File:** `includes/class-updater.php:189-191`
**Issue:** `WP_Filesystem::move()` returns `false` on failure. If the move fails (permissions, locked files, destination already exists), the method silently returns `$response` as if the update succeeded. WordPress then tries to activate the plugin from the original (now stale) location, or worse, from a partially-moved directory. This can leave the plugin in a broken state with no admin-visible error.

**Fix:**
```php
$moved = $wp_filesystem->move($result['destination'], $correct_folder);
if (!$moved) {
    return new WP_Error(
        'rg_post_install_failed',
        __('Responsive Goodies: could not move plugin to correct directory after update.', 'responsive-goodies')
    );
}
$result['destination'] = $correct_folder;
```

---

## Warnings

### WR-01: Field title output in `render_settings_section_fields` is unescaped

**File:** `includes/class-settings.php:149, 151`
**Issue:** `$field['title']` is echoed directly with no escaping in both the `label_for` and the plain `<th>` branches. Field titles are registered via `add_settings_field()` which accepts arbitrary strings. If a title ever contains `<`, `>`, or `"`, this is an XSS vector in the admin UI (low severity given `manage_options` capability, but still a defect).

**Fix:**
```php
echo '<th scope="row"><label for="' . esc_attr($field['args']['label_for']) . '">' . esc_html($field['title']) . '</label></th>';
// and
echo '<th scope="row">' . esc_html($field['title']) . '</th>';
```

---

### WR-02: `get_remote_version` does not check the HTTP response code before decoding

**File:** `includes/class-updater.php:77-88`
**Issue:** `get_github_changelog()` in `class-changelog.php` correctly checks `wp_remote_retrieve_response_code($request) !== 200` before decoding. `get_remote_version()` in `class-updater.php` does not — it proceeds to `json_decode` on any non-WP_Error response, including GitHub 403 (rate-limited) or 404 responses. A rate-limit response body will not contain `tag_name`, so it returns `false`, but the false result is not cached, meaning the next pageload immediately makes another rate-limited request.

**Fix:**
```php
if (wp_remote_retrieve_response_code($request) !== 200) {
    return false;
}
```
Add this check after the `is_wp_error` guard on line 77.

---

### WR-03: Divi detection uses loose comparison (`==`) on a GET parameter

**File:** `includes/class-responsive-goodies.php:70`
**Issue:** `$_GET['et_fb'] == '1'` uses loose comparison. A request with `?et_fb[]=1` would cause PHP to compare an array to the string `'1'`, producing a notice in PHP 8.x and unpredictable coercion. WordPress suppresses some notices but this is a type-safety defect.

**Fix:**
```php
if (isset($_GET['et_fb']) && $_GET['et_fb'] === '1') {
```

---

### WR-04: `orphan_fix_enabled_callback` tooltip text describes the wrong field

**File:** `includes/class-settings.php:170-171`
**Issue:** The tooltip on the "Enable Orphan Fix" toggle reads: *"Minimum number of words required on the last line before orphan fix is applied (2-10)"*. This describes the `orphan_fix_max_words` field, not the enable toggle. The enable toggle should describe what the feature does.

**Fix:** Change the tooltip text to describe the Orphan Fix feature:
```php
<span class="rg-tooltip-text">Prevent single words appearing alone on the last line of paragraphs and headings.</span>
```

---

## Info

### IN-01: `get_changelog` in `class-updater.php` does not sanitise release body before caching

**File:** `includes/class-updater.php:159-161`
**Issue:** `$release['body']` is passed through `wp_kses_post()` inline when building the string, but the markdown is not converted before this — it is stored and displayed as raw markdown inside a `<p>` tag. Unlike `class-changelog.php` which calls `convert_markdown_to_html()`, the updater's `get_changelog()` renders raw markdown text directly. This is inconsistent and produces poor display in the WordPress update modal.

**Suggestion:** Either call a markdown converter before wrapping in `<p>`, or accept that the update modal shows raw markdown and document that limitation. The inconsistency is the defect worth resolving.

---

### IN-02: CHANGELOG.md — `[0.3.8]` entry has no date

**File:** `CHANGELOG.md:25`
**Issue:** Keep a Changelog format requires a date for every release entry: `## [0.3.8] - YYYY-MM-DD`. The current entry reads `## [0.3.8] - Previous release`, which is not compliant and breaks any tooling that parses the format.

**Suggestion:** Replace with the actual release date, or omit the entry entirely and rely on GitHub releases for pre-0.4.0 history.

---

### IN-03: Trailing whitespace and mixed indentation in `class-responsive-goodies.php` and `class-settings.php`

**File:** `includes/class-responsive-goodies.php:58-59`, `includes/class-settings.php:297-298`
**Issue:** Both files have blank lines containing only tab characters (mixed indentation artefacts), visible at the end of method blocks. Not a functional defect, but inconsistent with the rest of the file which uses 4-space indentation.

**Suggestion:** Strip trailing whitespace from those lines. No logic change required.

---

_Reviewed: 2026-05-25_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_

---

## Fixes Applied

All Critical and Warning findings fixed on 2026-05-25.

| Finding | Description | Commit |
|---------|-------------|--------|
| CR-01 | `plugin_info()` now returns `$res` (not `false`) for non-matching requests, preserving the filter chain for all other plugins | 695a744 |
| CR-02 | `post_install()` checks `$wp_filesystem->move()` return value and returns a `WP_Error` on failure | 546dd1c |
| WR-01 | `$field['title']` wrapped with `esc_html()` in both branches of `render_settings_section_fields()` | ce12770 |
| WR-02 | `get_remote_version()` checks `wp_remote_retrieve_response_code()` for 200 before decoding JSON | 61d5cf3 |
| WR-03 | Divi `et_fb` GET param comparison changed from loose `==` to strict `===` with string cast | a8eb3f5 |
| WR-04 | "Enable Orphan Fix" tooltip corrected to describe the toggle, not the `max_words` field | 5b875ac |

Info findings (IN-01, IN-02, IN-03) were out of scope for this fix pass and remain open for review.
