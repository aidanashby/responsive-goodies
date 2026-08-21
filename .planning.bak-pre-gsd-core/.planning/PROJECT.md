# Responsive Goodies — Quality Refactor

## What This Is

A planned quality pass on the Responsive Goodies WordPress plugin (v0.3.8). No new features — the goal is to leave the codebase cleaner, more consistent, more secure, and free of known bugs, with a version bump and changelog entry to mark the work.

## Core Value

Every feature works correctly and the codebase is maintainable — naming is consistent, capability checks are in place, dead code is gone, obvious documentation gaps are filled, and the confirmed changelog bug is fixed.

## Context

**Plugin:** Responsive Goodies v0.3.8 — a collection of five opt-in responsive utilities for WordPress sites.

**Architecture:** PHP + jQuery, no build pipeline. Central orchestrator (`class-responsive-goodies.php`) instantiates five self-contained feature classes under `includes/features/`. Self-update delivered via GitHub Releases API. Divi 5 compatibility baked in.

**Five active features (all in scope, none being removed):**
1. Orphan Fix — prevents typographic orphans in text
2. Device Menu — adds device-type classes to nav menus
3. Disable Hover — disables hover states on touch devices
4. Prevent Scroll — locks body scroll on demand
5. Back to Top — adds a scroll-to-top button (targets `.et_pb_scroll_top` for Divi)

**Target environment:** PHP 8.x + WordPress 6.x only. No backwards-compat requirement for PHP 7.x.

## Requirements

### Validated

- ✓ Five opt-in features, each self-contained with its own class, assets, and hooks — existing
- ✓ Single `responsive_goodies_options` array in `wp_options` controls all feature toggles — existing
- ✓ Settings page under WordPress Settings API — existing
- ✓ Self-update via GitHub Releases API using WP updater filters — existing
- ✓ Divi 5 visual builder compatibility (all hooks suppressed during editing) — existing
- ✓ `manage_options` capability check on settings page registration — existing

### Active

- [ ] **BUG-01**: `get_github_changelog()` body is incomplete — fix so changelog modal renders correctly
- [ ] **QUAL-01**: Naming consistency — class/method names follow a single convention throughout
- [ ] **QUAL-02**: Hook and filter names follow a consistent prefix and pattern
- [ ] **QUAL-03**: `wp_options` key and settings field names are consistent
- [ ] **QUAL-04**: Capability checks present on all admin-only actions (settings save, AJAX, meta saves)
- [ ] **QUAL-05**: Dead code removed (unused methods, unreachable branches, commented-out blocks)
- [ ] **QUAL-06**: PHP 8.x modernisation — use typed properties, match expressions, null-safe operators where beneficial
- [ ] **QUAL-07**: Speed — remove unnecessary queries, defer/conditionally load assets
- [ ] **QUAL-08**: PHPDoc gaps filled on complex or non-obvious methods
- [ ] **QUAL-09**: `console.log` debug statements removed from production JS
- [ ] **REL-01**: `CHANGELOG.md` updated with refactor entries
- [ ] **REL-02**: Version bumped consistently across plugin header, constant, and any other version references

### Out of Scope

- New features or behaviour changes — internal improvement only
- Output sanitisation / escaping audit — existing `sanitize_text_field` / `absint` coverage is sufficient for now
- Nonce verification on forms/AJAX — not in scope this pass
- Full PHPDoc coverage — only obvious gaps, not every method
- PHP 7.x compatibility — dropped by design

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| PHP 8.x only | Simplifies code; no need for 7.x compat shims | Drop polyfills / workarounds if any exist |
| Keep all five features | All are actively used | No removal |
| Capability checks only (not full security audit) | Targeted scope | Focus on `current_user_can()` coverage |
| PHPDoc: gaps only | Full coverage adds noise without value at this scale | Document the non-obvious; skip boilerplate |
| Include version bump + CHANGELOG.md | Marks the work as a discrete release | Bump to 0.4.0 (pending review of change significance) |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd-transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd-complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-05-14 after initialization*
