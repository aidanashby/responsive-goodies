# Roadmap: Responsive Goodies Quality Refactor

## Overview

A sequential quality pass on Responsive Goodies v0.3.8 — fixing one confirmed bug, then improving naming consistency, removing dead code, modernising to PHP 8.x, optimising asset loading, and closing out with documentation and a version bump to 0.4.0. Each phase builds on the last: names are locked before types are added, dead code is removed before annotation, and performance changes land on a stable hook structure.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [x] **Phase 1: Bug Fix** - Fix the changelog modal so it renders HTML content correctly
- [x] **Phase 2: Naming Consistency** - Lock class, hook, and options key naming before any structural changes
- [x] **Phase 3: Dead Code + Capability Checks** - Remove dead code and gate all admin-only actions
- [x] **Phase 4: PHP 8.x Modernisation** - Apply typed properties, return types, match, nullsafe, and ?? throughout
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
**Plans**: 1 plan
Plans:
- [x] 01-01-PLAN.md — Fix convert_markdown_to_html() ul regex and complete get_github_changelog()

### Phase 2: Naming Consistency
**Goal**: All class names, hook registrations, and options field names follow a single consistent convention — locked in place before typed properties or annotations are added
**Depends on**: Phase 1
**Requirements**: QUAL-01, QUAL-02, QUAL-03
**Success Criteria** (what must be TRUE):
  1. All classes carry a `Responsive_Goodies_` prefix with PascalCase; all methods are snake_case; PHPCS WordPress ruleset reports zero naming violations
  2. All internal hook registrations use the `responsive_goodies_` prefix; no public `do_action()` or `apply_filters()` call names have changed
  3. All settings field names and `wp_options` array keys are consistent snake_case; the top-level `responsive_goodies_options` key is unchanged
**Plans**: 1 plan
Plans:
- [x] 02-01-PLAN.md — Grep-based naming audit; fix any violations found

### Phase 3: Dead Code + Capability Checks
**Goal**: The codebase contains only live, reachable code and every admin-only action is gated by a capability check
**Depends on**: Phase 2
**Requirements**: QUAL-04, QUAL-05
**Success Criteria** (what must be TRUE):
  1. Every `wp_ajax_*` handler calls `current_user_can( 'manage_options' )` at entry (confirmed N/A — no AJAX handlers exist)
  2. No stub JS files or their enqueue calls remain; deactivate() body is documented
**Plans**: 1 plan
Plans:
- [x] 03-01-PLAN.md — Delete frontend.js + admin.js stubs, remove their enqueue calls, document deactivate()

### Phase 4: PHP 8.x Modernisation
**Goal**: Pragmatic PHP 8.x subset — ?? operator replaces isset() ternaries; return type declarations added to all non-trivial methods
**Depends on**: Phase 3
**Requirements**: QUAL-06
**Success Criteria** (what must be TRUE):
  1. All `isset($x) ? $x : $default` patterns replaced with `$x ?? $default` throughout
  2. All non-trivial methods carry return type declarations
  3. No typed properties or match expressions added (out of scope for pragmatic subset)
**Plans**: 1 plan
Plans:
- [x] 04-01-PLAN.md — ?? replacement throughout + return types on all non-trivial methods

### Phase 5: Performance + Asset Loading
**Goal**: Frontend and admin assets load only when needed; the GitHub update check is transient-guarded; frontend scripts use the defer strategy
**Depends on**: Phase 4
**Requirements**: QUAL-07
**Success Criteria** (what must be TRUE):
  1. Frontend CSS enqueues only when at least one feature is enabled
  2. GitHub update check transient-guarded at 6h (rg_update_check); get_changelog() guarded at 12h (rg_changelog_info)
  3. All frontend scripts registered with defer strategy (no wp_add_inline_script conflicts confirmed)
**Plans**: 1 plan
Plans:
- [ ] 05-01-PLAN.md — Transient guard on updater API calls, conditional CSS enqueue, defer strategy on feature scripts

### Phase 6: Docs + Release
**Goal**: PHPDoc gaps are filled, debug output is gone from all JS files, the changelog records the full refactor, and the version is bumped consistently to 0.4.0
**Depends on**: Phase 5
**Requirements**: QUAL-08, QUAL-09, REL-01, REL-02
**Success Criteria** (what must be TRUE):
  1. PHPDoc on get_github_changelog(), convert_markdown_to_html(), is_divi_builder_active(), sanitize_options(), get_remote_version(), get_changelog()
  2. Zero console.log in any shipped JS file
  3. CHANGELOG.md exists with 0.4.0 entry in Keep a Changelog format
  4. grep -r "0\.3\.8" . returns zero results
**Plans**: 1 plan
Plans:
- [ ] 06-01-PLAN.md — PHPDoc, console.log audit, CHANGELOG.md, version bump to 0.4.0

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 4 → 5 → 6

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Bug Fix | 1/1 | Complete | 2026-05-14 |
| 2. Naming Consistency | 1/1 | Complete | 2026-05-24 |
| 3. Dead Code + Capability Checks | 1/1 | Complete | 2026-05-24 |
| 4. PHP 8.x Modernisation | 1/1 | Complete | 2026-05-24 |
| 5. Performance + Asset Loading | 0/1 | Ready to execute | - |
| 6. Docs + Release | 0/1 | Ready to execute | - |
