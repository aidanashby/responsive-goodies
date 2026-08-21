# Requirements — Responsive Goodies Quality Refactor

## v1 Requirements

### Bug Fixes

- [ ] **BUG-01**: Complete `get_github_changelog()` so the changelog modal renders HTML content; add explicit TTL (`12 * HOUR_IN_SECONDS`) to the `rg_github_changelog` transient; verify fallback GitHub link still renders on API failure

### Naming Consistency

- [ ] **QUAL-01**: All class and method names follow WordPress coding standards — PascalCase classes with `Responsive_Goodies_` prefix, snake_case methods; zero PHPCS WordPress ruleset violations
- [ ] **QUAL-02**: All internal hook registrations use consistent `responsive_goodies_` prefix; no public `do_action()`/`apply_filters()` call names are changed
- [ ] **QUAL-03**: Settings field names and `wp_options` array keys are consistent snake_case; top-level key `responsive_goodies_options` is unchanged

### Security

- [ ] **QUAL-04**: All `wp_ajax_*` handlers have `current_user_can( 'manage_options' )` check at entry; any other privileged operation gated by capability check

### Code Quality

- [ ] **QUAL-05**: Dead code removed — no unrooted methods, no commented-out code blocks, no unreachable branches, no debug functions; empty deactivation hook body resolved with comment or cleanup logic
- [ ] **QUAL-06**: PHP 8.x modernisation applied in order — typed properties (with default or `?type` for nullable), return type declarations, match expressions where appropriate, nullsafe operator for optional chains, `??` replacing `isset()` ternaries, `readonly` on constructor-assigned constants
- [ ] **QUAL-07**: Frontend assets conditionally enqueued (only when at least one feature enabled); admin assets scoped to settings page; update check wrapped in transient guard (6–12 hours); `defer` strategy applied to frontend scripts after inline script audit
- [ ] **QUAL-08**: PHPDoc added to complex/non-obvious methods (GitHub API parsing, Divi detection, changelog construction, options sanitisation); trivial methods left undocumented
- [ ] **QUAL-09**: Zero `console.log` in all shipped JS files; `console.error`/`console.warn` acceptable only with explanatory comment

### Release

- [ ] **REL-01**: `CHANGELOG.md` created/updated with entry at top in Keep a Changelog format for version `0.4.0`, covering: Bug Fixes (BUG-01), Changed (naming, PHP 8.x, performance), Removed (dead code, debug statements)
- [ ] **REL-02**: Version bumped to `0.4.0` consistently in plugin header (`Version:` field), `RESPONSIVE_GOODIES_VERSION` constant, and `CHANGELOG.md`; `grep -r "0\.3\.8" .` returns zero results

---

## v2 Requirements (deferred)

- Output sanitisation / escaping audit — existing `sanitize_text_field` / `absint` coverage is sufficient; full audit deferred
- Nonce verification on all forms/AJAX — not in scope this pass
- Full PHPDoc coverage on all methods — only gap-filling in v1
- `wp_admin_notice()` migration — only if admin notices confirmed to exist; evaluate in Phase 5
- PHP 7.x compatibility — dropped by design; not a future requirement

---

## Out of Scope

- New features or behaviour changes — internal improvement only
- Removing any of the five existing features — all are active
- Renaming the top-level `responsive_goodies_options` key — would wipe live user settings
- Renaming any public `do_action()` / `apply_filters()` hooks — breaking change for third-party customisations
- Constructor promotion on feature classes — constructors call `get_option()`, not injected params
- Enums or Fibers — structural changes beyond a quality pass

---

## Dependency Chain

```
QUAL-01 → QUAL-02  (hook names derive from class naming conventions)
QUAL-03 → QUAL-04  (capability checks reference correct option keys)
QUAL-06 → QUAL-05  (PHP 8.x modernisation may reveal compat shims as dead code)
BUG-01  → REL-01   (fix must be documented in changelog)
REL-01  → REL-02   (changelog version must match bumped constant)
```

---

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| BUG-01 | Phase 1 | Pending |
| QUAL-01 | Phase 2 | Pending |
| QUAL-02 | Phase 2 | Pending |
| QUAL-03 | Phase 2 | Pending |
| QUAL-04 | Phase 3 | Pending |
| QUAL-05 | Phase 3 | Pending |
| QUAL-06 | Phase 4 | Pending |
| QUAL-07 | Phase 5 | Pending |
| QUAL-08 | Phase 6 | Pending |
| QUAL-09 | Phase 6 | Pending |
| REL-01 | Phase 6 | Pending |
| REL-02 | Phase 6 | Pending |
