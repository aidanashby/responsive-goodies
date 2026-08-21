# Pitfalls Research — Responsive Goodies Refactor

## Summary

10 pitfalls across 5 risk areas. 5 critical, 3 moderate, 2 minor.

---

## Critical Pitfalls

### 1. Option key rename wipes live settings (QUAL-03)
If `responsive_goodies_options` (the top-level `wp_options` key) is renamed, every live installation resets to defaults silently. Users lose all their feature toggle settings.

**Warning sign:** Any diff touching the `get_option('responsive_goodies_options')` / `update_option('responsive_goodies_options')` calls.

**Prevention:** Scope QUAL-03 strictly to array field names within the options array — never the top-level key. Any top-level key rename requires a one-time migration function with a version flag guard (`get_option('rg_migrated_v040')`).

**Phase:** QUAL-03 (naming consistency)

---

### 2. `upgrader_post_install` folder mismatch deactivates plugin post-update (REL-02)
GitHub zip archives extract to `{repo}-{tag}/`. If the folder-name constant in the updater's `upgrader_post_install` hook drifts from the actual directory name, WordPress cannot find the plugin after update and silently deactivates it.

**Warning sign:** Any change to folder-name constants in `class-updater.php`.

**Prevention:** Treat the `upgrader_post_install` hook folder constant as locked. Test the full update cycle locally (install → trigger update check → apply update → verify active) before tagging a release.

**Phase:** REL-02 (version bump / release)

---

### 3. Update check fires twice with no caching — GitHub rate-limit risk (QUAL-07)
WordPress calls `set_site_transient` twice per update check cycle. The updater has no transient caching on the API call (confirmed from codebase scan), so two GitHub API calls fire on every check. GitHub's unauthenticated API rate limit is 60 requests/hour per IP — shared hosting sites under load could hit this.

**Warning sign:** `class-updater.php` contains `wp_remote_get` with no transient guard wrapping it.

**Prevention:** Add a 6–12 hour transient guard to the update check in `class-updater.php`, matching the pattern already used in `class-changelog.php`.

**Phase:** QUAL-07 (speed / unnecessary queries)

---

### 4. Typed properties fatal if uninitialised (QUAL-06)
PHP 8.x typed properties throw a fatal `TypeError` when read before assignment — not a silent null like untyped properties. Existing code that reads a property conditionally set in one code path but not another will fatal.

**Warning sign:** Any typed property without a default value or nullable (`?type`) declaration.

**Prevention:** Declare all new typed properties with either a default value or as nullable. Trace every code path that reads newly typed properties before assigning. Never add typed properties in a bulk pass without reviewing read sites.

**Phase:** QUAL-06 (PHP 8.x modernisation)

---

### 5. Hook/filter renaming breaks third-party customisations (QUAL-02)
Renaming a `do_action()` or `apply_filters()` call that this plugin registers silently breaks any site with custom `add_action()` / `add_filter()` calls targeting it. Users would lose customisations with no error.

**Warning sign:** Any diff touching `do_action('responsive_goodies_*')` or `apply_filters('responsive_goodies_*')` calls.

**Prevention:** Scope QUAL-02 to internal method names only. Any public hook rename is a breaking change and must be deferred or handled with a deprecated alias for at least one major version.

**Phase:** QUAL-02 (hook naming consistency)

---

## Moderate Pitfalls

### 6. `rg_github_changelog` transient has no TTL (BUG-01)
The changelog transient is set but with no expiry (`false` or `0` TTL), meaning it persists forever. After a new release, the cached empty/broken changelog will never refresh — users will keep seeing the broken modal even after BUG-01 is fixed.

**Prevention:** Add explicit TTL (`12 * HOUR_IN_SECONDS`) when writing the transient. Delete the transient on plugin update activation.

**Phase:** BUG-01 (changelog fix)

---

### 7. Empty deactivation hook silently deleted as dead code (QUAL-05)
The plugin registers a deactivation hook with an empty body. This may be intentional (placeholder for future cleanup). Removing it silently as "dead code" is a mistake if there's a later expectation it exists.

**Prevention:** Either add a clarifying comment explaining it's intentional, add minimal cleanup logic (e.g. delete the changelog transient on deactivation), or explicitly note the removal decision in CHANGELOG.md.

**Phase:** QUAL-05 (dead code removal)

---

### 8. `console.log` removal misses non-literal constructions (QUAL-09)
A simple search for `console.log` in `frontend.js` only may miss dynamic constructions (`console['log']`, variables, etc.) in other JS files.

**Prevention:** Grep all JS files across the plugin directory for `console` before marking QUAL-09 complete.

**Phase:** QUAL-09 (debug statement removal)

---

## Minor Pitfalls

### 9. Version string missed in one location (REL-02)
The version number appears in multiple places (plugin header, `RESPONSIVE_GOODIES_VERSION` constant, possibly `readme.txt` or `package.json` if added). Missing one creates inconsistency that breaks update detection.

**Prevention:** Grep for `0.3.8` (and the new version number) across the entire plugin directory before tagging. Check plugin header, constant definition, and any other version references.

**Phase:** REL-02 (version bump)

---

### 10. PHP 8.4 implicit nullable deprecations introduced during QUAL-06
When adding typed parameters, `function foo(string $param = null)` is deprecated in PHP 8.4 in favour of `function foo(?string $param = null)`. Refactoring QUAL-06 without awareness of this will introduce deprecation notices on PHP 8.4 sites.

**Prevention:** Use `?type $param = null` syntax throughout when adding nullable typed parameters. Run `php -l` on all changed files after QUAL-06 work.

**Phase:** QUAL-06 (PHP 8.x modernisation)

---

## Phase Warning Map

| Pitfall | Requirement | Severity |
|---------|------------|---------|
| Option key rename wipes settings | QUAL-03 | Critical |
| `upgrader_post_install` folder mismatch | REL-02 | Critical |
| No transient on update check | QUAL-07 | Critical |
| Typed property uninitialised fatal | QUAL-06 | Critical |
| Public hook rename breaks customisations | QUAL-02 | Critical |
| Changelog transient no TTL | BUG-01 | Moderate |
| Empty deactivation hook removed silently | QUAL-05 | Moderate |
| `console.log` grep scope | QUAL-09 | Moderate |
| Version string missed in one location | REL-02 | Minor |
| PHP 8.4 implicit nullable deprecation | QUAL-06 | Minor |
