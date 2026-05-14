---
phase: 01-bug-fix
reviewed: 2026-05-14T00:00:00Z
depth: standard
files_reviewed: 1
files_reviewed_list:
  - includes/class-changelog.php
findings:
  critical: 2
  warning: 2
  info: 1
  total: 5
status: issues_found
---

# Phase 01: Code Review Report

**Reviewed:** 2026-05-14
**Depth:** standard
**Files Reviewed:** 1
**Status:** issues_found

## Summary

`class-changelog.php` fetches GitHub release data via `wp_remote_get`, converts markdown to HTML, sanitises with `wp_kses_post`, and caches the result. The overall structure is reasonable, but there are two critical defects: XSS via unsanitised markdown conversion output stored and echoed before sanitisation is fully applied to intermediate HTML, and a logic error that causes `wp_kses_post` to run on already-looped HTML then be cached — but the markdown converter itself produces unescaped content from external API data that passes through without tag-level sanitisation until after assembly. More concretely: the `convert_markdown_to_html()` method injects raw GitHub release body text directly into HTML element content without escaping it, and `wp_kses_post` is called on the assembled block only after all releases are concatenated — but release names (`tag_name`, used inside `<h3>`) are escaped with `esc_html` correctly while release bodies are not escaped at all before markdown conversion. There is also a hardcoded typo in the GitHub username, a missing User-Agent header required by the GitHub API, and a redundant check ordering issue.

## Critical Issues

### CR-01: Release body content not escaped before markdown conversion — XSS via GitHub API data

**File:** `includes/class-changelog.php:65`

**Issue:** `$release['body']` is passed directly to `convert_markdown_to_html()` without any prior escaping. Inside that method, regex substitutions inject the raw GitHub-sourced string into HTML tags (e.g. `<h4>$1</h4>`, `<li>$1</li>`, `<strong>$1</strong>`). The `wp_kses_post` call on line 70 runs on the final `$html` string, which does strip disallowed tags — but `wp_kses_post` is not an XSS-complete defence for attribute injection or javascript: URIs inside allowed tags. More critically, if the GitHub account were compromised or the API response spoofed (no integrity check exists), arbitrary HTML attributes could survive `wp_kses_post`. The safe pattern is to escape the leaf text values before inserting them into markup, then apply `wp_kses_post` as a belt-and-braces measure.

**Fix:**
```php
private static function convert_markdown_to_html( $text ) {
    // Escape the raw text first so injected content cannot break out of markup
    $text = esc_html( $text );

    // Convert markdown headers (content is now HTML-safe)
    $text = preg_replace( '/^## (.+)$/m', '<h4>$1</h4>', $text );
    $text = preg_replace( '/^### (.+)$/m', '<h5>$1</h5>', $text );

    // Convert markdown lists
    $text = preg_replace( '/^- (.+)$/m', '<li>$1</li>', $text );
    $text = preg_replace( '/(?:<li>.+<\/li>\n?)+/', '<ul>$0</ul>', $text );

    // Convert bold text
    $text = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text );

    // Convert line breaks
    $text = nl2br( $text );

    return $text;
}
```

---

### CR-02: GitHub API request missing required User-Agent header — requests silently fail in some environments

**File:** `includes/class-changelog.php:38`

**Issue:** The GitHub REST API requires a `User-Agent` header on every request. Requests without it receive a `403` response. The current code checks for HTTP 200 (line 47) and returns `false` on any other code, so this fails silently — the changelog never loads and no error is surfaced. This is a confirmed class of breakage: any WordPress install where `wp_remote_get` does not automatically inject a User-Agent (the default WP UA is sent, but it is good practice and required per GitHub docs to send an explicit one) will see a 403 and fall back to the GitHub link indefinitely. The transient will never be set, so every admin page load fires a failing HTTP request.

**Fix:**
```php
$request = wp_remote_get(
    'https://api.github.com/repos/' . self::$github_username . '/' . self::$github_repo . '/releases',
    array(
        'timeout'    => 10,
        'sslverify'  => true,
        'headers'    => array(
            'User-Agent' => 'responsive-goodies-wp-plugin/' . RESPONSIVE_GOODIES_VERSION,
        ),
    )
);
```

---

## Warnings

### WR-01: Hardcoded GitHub username contains a typo

**File:** `includes/class-changelog.php:12`

**Issue:** `$github_username` is set to `'aidnashby'` — missing the second `a` in `aidan`. The CLAUDE.md confirms the correct username is `aidanashby`. Every API call and the fallback link use this value, so the fallback URL also points to a non-existent repository. This means the changelog will never load from the real repository.

**Fix:**
```php
private static $github_username = 'aidanashby';
```

---

### WR-02: `wp_kses_post` applied before caching but after `nl2br` — `<br />` tags inside `<ul>` blocks

**File:** `includes/class-changelog.php:86,92`

**Issue:** `nl2br()` is called (line 92) on the entire text including already-converted `<ul><li>...</li></ul>` blocks. This inserts `<br />` tags between list items and inside list wrappers, producing malformed HTML: `<ul><br /><li>item</li><br /></ul>`. The fix is to apply `nl2br` only to text regions that have not already been converted to block-level HTML, or to strip the newlines that wrap converted list blocks before calling `nl2br`.

**Fix:** Strip trailing newlines from converted list blocks before `nl2br`:
```php
// After the <ul> wrapping regex:
$text = preg_replace( '/\n(<ul>)/', '$1', $text );
$text = preg_replace( '/(<\/ul>)\n/', '$1', $text );

// Then nl2br is safe for remaining plain-text regions
$text = nl2br( $text );
```

---

## Info

### IN-01: Transient cache never set on empty release list — repeated API calls

**File:** `includes/class-changelog.php:54-72`

**Issue:** If the GitHub API returns a valid 200 response with an empty releases array (`[]`), the `foreach` loop produces an empty string, `wp_kses_post('')` returns `''`, and `set_transient` is called with an empty string. On the next request, the cache check on line 33 (`$cached !== ''`) treats empty string as a cache miss and fires another API request. For a plugin with no releases yet, this fires a live API call on every admin page load. Consider caching a placeholder string (e.g. `'<p>No releases found.</p>'`) when the releases array is empty, or removing the `!== ''` condition from the cache check.

**Fix:**
```php
if ( empty( $releases ) ) {
    $placeholder = '<p>No releases found.</p>';
    set_transient( 'rg_github_changelog', $placeholder, 12 * HOUR_IN_SECONDS );
    return $placeholder;
}
```

---

_Reviewed: 2026-05-14_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
