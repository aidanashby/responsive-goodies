# Phase 1: Bug Fix - Context

**Gathered:** 2026-05-14
**Status:** Ready for planning

<domain>
## Phase Boundary

Complete `get_github_changelog()` in `includes/class-changelog.php` so the changelog modal displays rendered HTML from the GitHub Releases API, caches it with a 12-hour TTL, and falls back to a GitHub link on failure. One file, one method — no feature changes.

</domain>

<decisions>
## Implementation Decisions

### Release scope
- **D-01:** Show the last 3 releases — slice `$releases` array to the first 3 entries.
- **D-02:** Each release entry shows: tag name as a heading + release body text. No published date.

### Markdown rendering
- **D-03:** Fix and use the existing `convert_markdown_to_html()` method. Repair the `<ul>` wrapping regex (currently uses a greedy `/s` match that merges separate lists). Keep all other conversions (headers → h4/h5, bold → strong, nl2br).

### Claude's Discretion
- HTML structure/wrapping for each release entry (e.g., whether to use a `<div>` per release, separator between entries).
- Whether to sanitize output through `wp_kses_post()` before returning.

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Source file
- `includes/class-changelog.php` — The file being fixed. Contains `get_github_changelog()` (incomplete stub ending at comment), `convert_markdown_to_html()` (buggy `<ul>` regex), `display_changelog()` (fallback link already present).

### Requirements
- `.planning/REQUIREMENTS.md` §BUG-01 — Full requirement: complete `get_github_changelog()`, add `12 * HOUR_IN_SECONDS` TTL to `rg_github_changelog` transient, verify fallback renders on API failure.

### Architecture context
- `.planning/codebase/ARCHITECTURE.md` §Infrastructure Classes — Describes `Responsive_Goodies_Changelog` role and error handling patterns.

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `convert_markdown_to_html()` — Already present in `class-changelog.php`. Handles `##`/`###` headers, `- ` list items, `**bold**`, and line breaks. Needs its `<ul>` regex fixed before use.
- `display_changelog()` — Already calls `get_github_changelog()` and renders the fallback link (`<p>Unable to load changelog. <a href="...">View on GitHub</a></p>`) when the method returns falsy. No changes needed here.

### Established Patterns
- `wp_remote_get` + `is_wp_error()` + response code 200 check + `json_decode` + `json_last_error()` — all already in place in `get_github_changelog()`. The fix only needs to add the HTML-building loop and `set_transient()` after the existing validation block.
- Transient key: `rg_github_changelog` (already used in `get_transient()` call — must match in `set_transient()`).
- No exceptions in this codebase — the `try/catch` wrapping in `get_github_changelog()` is the exception (pun intended); the pattern elsewhere is guard clauses returning `false`.

### Integration Points
- `get_github_changelog()` is called only by `display_changelog()`, which is called as a settings-page callback. Output is echoed directly — the method must return an HTML string or `false`.
- `clear_changelog_cache()` deletes the transient — this will continue to work once `set_transient()` is added.

</code_context>

<specifics>
## Specific Ideas

No specific requirements — open to standard approaches for HTML structure.

</specifics>

<deferred>
## Deferred Ideas

None — discussion stayed within phase scope.

</deferred>

---

*Phase: 1-Bug Fix*
*Context gathered: 2026-05-14*
