# Roadmap: Responsive Goodies Quality Refactor

## Overview

A sequential quality pass on Responsive Goodies v0.3.8 — fixing one confirmed bug, then improving naming consistency, removing dead code, modernising to PHP 8.x, optimising asset loading, and closing out with documentation and a version bump to 0.4.0. Each phase builds on the last: names are locked before types are added, dead code is removed before annotation, and performance changes land on a stable hook structure.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [ ] **Phase 1: Bug Fix** - Fix the changelog modal so it renders HTML content correctly
- [ ] **Phase 2: Naming Consistency** - Lock class, hook, and options key naming before any structural changes
- [ ] **Phase 3: Dead Code + Capability Checks** - Remove dead code and gate all admin-only actions
- [ ] **Phase 4: PHP 8.x Modernisation** - Apply typed properties, return types, match, nullsafe, and ?? throughout
- [ ] **Phase 5: Performance + Asset Loading** - Conditionally enqueue assets and add transient guards
- [ ] **Phase 6: Docs + Release** - Fill PHPDoc gaps, strip debug statements, update changelog, bump version

## Phase Details

### Phase 1: Bug Fix
**Goal**: The changelog modal renders correctly with HTML content and a reliable transient cache
**Depends on**: Nothing (first phase)
**Requirements**: BUG-01
**Success Criteria** (what must be TRUE):
  1. The changelog modal displays formatted HTML content from the GitHub API response
  2. The `rg_github_changelog` transient is set with a 12-hour TTL on successful API response
  3. On API failure the modal renders a fallback GitHub link rather than blank content
**Plans**: TBD

### Phase 2: Naming Consistency
**Goal**: All class names, hook registrations, and options field names follow a single consistent convention — locked in place before typed properties or annotations are added
**Depends on**: Phase 1
**Requirements**: QUAL-01, QUAL-02, QUAL-03
**Success Criteria** (what must be TRUE):
  1. All classes carry a `Responsive_Goodies_` prefix with PascalCase; all methods are snake_case; PHPCS WordPress ruleset reports zero naming violations
  2. All internal hook registrations use the `responsive_goodies_` prefix; no public `do_action()` or `apply_filters()` call names have changed
  3. All settings field names and `wp_options` array keys are consistent snake_case; the top-level `responsive_goodies_options` key is unchanged
**Plans**: TBD

### Phase 3: Dead Code + Capability Checks
**Goal**: The codebase contains only live, reachable code and every admin-only action is gated by a capability check
**Depends on**: Phase 2
**Requirements**: QUAL-04, QUAL-05
**Success Criteria** (what must be TRUE):
  1. Every `wp_ajax_*` handler calls `current_user_can( 'manage_options' )` at entry and returns early on failure
  2. No unreachable branches, commented-out code blocks, unrooted methods, or debug functions remain in any PHP file
  3. The empty deactivation hook body is resolved — either removed or given a comment explaining its intentional absence
**Plans**: TBD

### Phase 4: PHP 8.x Modernisation
**Goal**: The codebase uses PHP 8.x features consistently — typed properties, return types, match expressions, nullsafe operators, and null coalescing — with no uninitialised property risk
**Depends on**: Phase 3
**Requirements**: QUAL-06
**Success Criteria** (what must be TRUE):
  1. All class properties carry type declarations; nullable properties use `?type` or a default value; no uninitialised typed property can cause a fatal error
  2. All non-trivial methods carry return type declarations
  3. `isset()` ternaries are replaced with `??`; appropriate chains use the nullsafe operator; closed-set value mappings use match expressions
  4. PHPCompatibility PHPCS ruleset reports zero PHP 7.x-only patterns remaining
**Plans**: TBD

### Phase 5: Performance + Asset Loading
**Goal**: Frontend and admin assets load only when needed; the GitHub update check is transient-guarded; frontend scripts use the defer strategy
**Depends on**: Phase 4
**Requirements**: QUAL-07
**Success Criteria** (what must be TRUE):
  1. Frontend scripts and styles are enqueued only when at least one feature is enabled; no assets load on pages where all features are disabled
  2. Admin assets are scoped to the plugin's settings page and do not load on other admin screens
  3. The GitHub update check is wrapped in a 6–12 hour transient guard; no more than one uncached API call fires per guard window
  4. Frontend scripts are registered with `[ 'strategy' => 'defer' ]` where no `wp_add_inline_script` in `after` position conflicts
**Plans**: TBD

### Phase 6: Docs + Release
**Goal**: PHPDoc gaps are filled, debug output is gone from all JS files, the changelog records the full refactor, and the version is bumped consistently to 0.4.0
**Depends on**: Phase 5
**Requirements**: QUAL-08, QUAL-09, REL-01, REL-02
**Success Criteria** (what must be TRUE):
  1. PHPDoc blocks exist on all complex or non-obvious methods (GitHub API parsing, Divi detection, changelog construction, options sanitisation); trivial methods are intentionally left undocumented
  2. Zero `console.log` calls remain in any shipped JS file; any retained `console.error` or `console.warn` has an explanatory comment
  3. `CHANGELOG.md` contains a `0.4.0` entry at the top in Keep a Changelog format covering Bug Fixes, Changed, and Removed sections
  4. `grep -r "0\.3\.8" .` returns zero results; version reads `0.4.0` in plugin header, `RESPONSIVE_GOODIES_VERSION` constant, and `CHANGELOG.md`
**Plans**: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 4 → 5 → 6

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Bug Fix | 0/? | Not started | - |
| 2. Naming Consistency | 0/? | Not started | - |
| 3. Dead Code + Capability Checks | 0/? | Not started | - |
| 4. PHP 8.x Modernisation | 0/? | Not started | - |
| 5. Performance + Asset Loading | 0/? | Not started | - |
| 6. Docs + Release | 0/? | Not started | - |
