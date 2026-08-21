# Phase 1: Bug Fix - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-05-14
**Phase:** 1-Bug Fix
**Areas discussed:** Release scope, Markdown rendering

---

## Release scope

**Question 1: How many releases should the modal display?**

| Option | Description | Selected |
|--------|-------------|----------|
| Latest release only | Show just the most recent entry | |
| Last 3 releases | Show the three most recent entries | ✓ |
| All releases | Show everything the API returns | |

**User's choice:** Last 3 releases

---

**Question 2: What information should each release entry show?**

| Option | Description | Selected |
|--------|-------------|----------|
| Version + body | Tag name as heading + release body | ✓ |
| Version + date + body | Also include published_at date | |
| You decide | Open to structural choices | |

**User's choice:** Version + body

---

## Markdown rendering

**Question: How should release body text be converted from Markdown to HTML?**

| Option | Description | Selected |
|--------|-------------|----------|
| Fix convert_markdown_to_html() | Repair the existing method's `<ul>` wrapping regex, keep headers/lists/bold | ✓ |
| Replace with wpautop() | WordPress built-in — handles paragraphs but loses header/list structure | |
| Use GitHub Markdown API | Second API call to /markdown — most accurate but adds HTTP request | |

**User's choice:** Fix convert_markdown_to_html()
**Notes:** The existing method already handles the patterns typical in GitHub release notes. The `<ul>` wrapping regex uses a greedy `/s` match that would merge separate lists — this needs fixing. Everything else (h4/h5 for ##/###, strong for **, nl2br) is correct.

---

## Claude's Discretion

- HTML structure/wrapping for each release entry (div-per-release, separators)
- Whether to pipe output through `wp_kses_post()` before returning

## Deferred Ideas

None — discussion stayed within phase scope.
