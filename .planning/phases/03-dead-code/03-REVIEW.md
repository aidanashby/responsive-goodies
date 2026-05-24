---
phase: 03-dead-code
reviewed: 2026-05-24T00:00:00Z
depth: standard
files_reviewed: 1
files_reviewed_list:
  - includes/class-responsive-goodies.php
findings:
  critical: 0
  warning: 3
  info: 2
  total: 5
status: issues_found
---

# Phase 03: Code Review Report

**Reviewed:** 2026-05-24
**Depth:** standard
**Files Reviewed:** 1
**Status:** issues_found

## Summary

One file reviewed: `includes/class-responsive-goodies.php`. The phase 3 changes (removing two `wp_enqueue_script` calls and adding a comment to `deactivate()`) are themselves clean. However, the review surfaced pre-existing logic bugs in `is_divi_builder_active()` and a potential class-not-loaded risk in `activate()`, plus two minor quality issues.

No security vulnerabilities introduced. No critical issues found.

## Warnings

### WR-01: Admin Divi check triggers on `et_fb=0`, incorrectly suppressing all features

**File:** `includes/class-responsive-goodies.php:70`
**Issue:** The condition `is_admin() && isset($_GET['et_fb'])` fires whenever `et_fb` is present in any admin URL, regardless of its value. Divi uses `et_fb=0` to indicate the builder is NOT active. Any admin page visited with `?et_fb=0` in the URL (e.g. via redirect or link) would cause this method to return `true`, blocking all feature initialisation for that request. The check is also fully redundant: line 65 already handles `et_fb == '1'` in all contexts.

**Fix:** Remove the entire admin block (lines 70-72). The `et_fb=1` check on line 65 and the `et_fb_is_enabled()` check on line 75 together cover all legitimate Divi builder detection:

```php
private function is_divi_builder_active() {
    if (isset($_GET['et_fb']) && $_GET['et_fb'] === '1') {
        return true;
    }
    if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
        return true;
    }
    return false;
}
```

---

### WR-02: Loose comparison `==` instead of strict `===` on `$_GET` value

**File:** `includes/class-responsive-goodies.php:65`
**Issue:** `$_GET['et_fb'] == '1'` uses loose comparison. While the practical impact is minimal here (the string `'1'` does not have surprising loose-equality matches that would cause a false positive in this context), it is inconsistent with PHP best practice for sanitised input comparison and could mask issues if the parameter type changes.

**Fix:** Use strict comparison:
```php
if (isset($_GET['et_fb']) && $_GET['et_fb'] === '1') {
```

---

### WR-03: `activate()` calls `Responsive_Goodies_Changelog::clear_changelog_cache()` before dependencies are loaded

**File:** `includes/class-responsive-goodies.php:125`
**Issue:** `activate()` is a static method called by WordPress's activation hook, which fires before `run()` and therefore before `load_dependencies()` requires `class-changelog.php`. If the entry point (`responsive-goodies.php`) does not explicitly require `class-changelog.php` before registering the activation hook, this call will produce a fatal `Class not found` error on plugin activation. This is a cross-file risk — confirm that `responsive-goodies.php` requires the changelog class unconditionally at bootstrap.

**Fix:** Either require the changelog class in `activate()` directly:
```php
public static function activate() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-changelog.php';
    // ... rest of activate
}
```
Or confirm the entry point requires it before `register_activation_hook()` is called, and document that dependency explicitly.

---

## Info

### IN-01: Duplicate `// Initialize features` comment

**File:** `includes/class-responsive-goodies.php:41` and `48`
**Issue:** The comment `// Initialize features` appears on both line 41 (before class instantiation) and line 48 (before the `init()` loop). They describe different operations. The second is misleading — it should distinguish calling `init()` from instantiation.

**Fix:**
```php
// Instantiate feature classes
$this->features['orphan_fix'] = new Responsive_Goodies_Orphan_Fix();
// ...

// Call init() on each enabled feature
foreach ($this->features as $feature) {
```

---

### IN-02: Stray mixed-indentation blank line

**File:** `includes/class-responsive-goodies.php:58-59`
**Issue:** There is a blank line with mixed tab and space characters between the closing brace of `run()` and the start of `is_divi_builder_active()`. Inconsistent with the surrounding file style.

**Fix:** Remove or normalise the extra blank line to a single empty line with consistent indentation.

---

_Reviewed: 2026-05-24_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
