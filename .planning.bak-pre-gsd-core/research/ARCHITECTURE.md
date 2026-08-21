# Architecture Patterns

**Project:** Responsive Goodies — Quality Refactor (v0.3.8 → 0.4.0)
**Researched:** 2026-05-14
**Confidence:** HIGH — patterns derived directly from codebase analysis + PHP 8.x official documentation

---

## What to Keep (Working Fine — Do Not Touch)

### Feature-flag orchestrator pattern
The central orchestrator (`class-responsive-goodies.php`) reading a single options array and conditionally calling `init()` on each feature class is correct and clean. It is the right pattern for a plugin of this size. Do not introduce a service container, dependency injection framework, or factory pattern — that would be over-engineering for five features.

### Guard-clause `init()` method
Each feature class returning early from `init()` if `!is_enabled()` is idiomatic WordPress PHP. Keep it exactly as-is.

### Single `responsive_goodies_options` array
One flat options array in `wp_options` is the right choice at this scale. Do not split into per-feature option keys — that would multiply DB reads for no benefit. Keep reading fresh from `get_option()` in each constructor.

### Self-contained feature directories
`includes/features/{feature}/` with co-located PHP, CSS, and JS is the correct structure. Do not flatten or reorganise.

### Divi builder suppression in orchestrator
`is_divi_builder_active()` check in `Responsive_Goodies::run()` is the correct place for this. Do not push it into individual feature classes.

### Passive error handling
`is_wp_error()` checks and graceful fallbacks on API calls are appropriate. Do not introduce exceptions or a logging layer.

---

## What to Improve and How

### 1. Typed properties on all feature classes (QUAL-06)

**Current state:** Properties declared without types (assumed — pre-8.0 style).

**Target:** Declare all class properties with types. PHP 8.0+ enforces these at runtime, catching bugs early.

```php
// Before
private $options;
private $enabled;

// After
private array $options;
private bool $enabled;
```

Apply to: all five feature classes and `Responsive_Goodies_Settings`.

**Build order implication:** Do this first. Typed properties surface any accidental type coercions that may exist — fix those before other changes.

### 2. Constructor property promotion where properties are injected or simple

**Current state:** Constructor assigns `$this->options = get_option(...)` — not a promotable pattern because the value comes from a function call, not a parameter. Do not force promotion here.

**Rule for this codebase:** Only use constructor promotion if a property is directly assigned from a constructor parameter. Since feature constructors read from `get_option()` rather than accepting injected dependencies, promotion is not appropriate for feature classes.

**Where it IS appropriate:** `Responsive_Goodies_Updater` and `Responsive_Goodies_Changelog` if they accept plugin file path or version as constructor parameters. Check each — promote only where the pattern already matches.

### 3. `match` expressions to replace `switch` or multi-branch `if` on option keys

**Current state:** Unknown — likely `if/else` chains in `sanitize_options()` and any feature-specific option handling.

**Target:** Where a single value is tested against discrete known values, prefer `match`. It is strict (no type coercion), exhaustive (throws `UnhandledMatchError` if no arm matches), and more readable.

```php
// Before
if ($key === 'back_to_top_desktop') {
    $clean[$key] = absint($value);
} elseif ($key === 'orphan_fix_max_words') {
    $clean[$key] = absint($value);
}

// After — only if the logic maps cleanly
$clean[$key] = match($key) {
    'back_to_top_desktop', 'orphan_fix_max_words' => absint($value),
    'orphan_fix_enabled', 'back_to_top_enabled'   => (bool) $value,
    default => sanitize_text_field($value),
};
```

Apply judiciously — only where the structure already resembles a dispatch on a known set of values. Do not force `match` onto conditionals that involve complex expressions.

### 4. Null-safe operator for option array access (QUAL-06)

**Current state:** Option keys likely accessed with `isset()` guards or direct array access.

**Target:** Where chained property/method access could produce a null, use `?->`. For array key access, prefer `$options['key'] ?? $default` (already valid PHP 7.4+, but confirm it is consistently used rather than `isset` + ternary).

### 5. Return type declarations on all non-trivial methods (QUAL-06)

**Current state:** Return types likely absent or inconsistent.

**Target:** Add return types to all public and protected methods. This is the single highest-value PHP 8.x improvement for a refactor — it documents intent and makes bugs visible at a glance.

```php
public function is_enabled(): bool
public function init(): void
public function get_options(): array
```

Apply to: all classes. `void` on `init()`, `bool` on `is_enabled()`, `void` on hook callbacks, `string|false` on API methods that can fail.

**Build order implication:** Do return types after typed properties — they depend on knowing what types properties actually hold.

### 6. Remove `frontend.js` stub or use it (QUAL-05 / QUAL-09)

**Current state:** `assets/js/frontend.js` is a stub that only logs to console. It is enqueued unconditionally on every frontend page load.

**Target:** Either remove the file and its enqueue call entirely, or move the `console.log` removal and keep the file only if a shared JS behaviour is needed. A stub that ships a network request and `console.log` on every page load is technical debt with a performance cost.

**Build order implication:** Decide this before auditing enqueue logic (QUAL-07) — the answer determines whether `enqueue_frontend_assets()` survives in its current form.

### 7. Conditional asset loading (QUAL-07)

**Current state:** `frontend.css` and `frontend.js` (currently a stub) are enqueued on every frontend page load unconditionally.

**Target:** If `frontend.css` contains only styles shared across multiple features, keep unconditional enqueue. If it can be scoped — or if all styles are already per-feature — move to conditional enqueue only when at least one feature is enabled.

Check: does `frontend.css` contain anything a site needs when zero features are enabled? If not, gate it behind an `array_filter` check on enabled features.

---

## Do Not Change (Scope Creep Risks)

| Item | Why Not |
|------|---------|
| Introduce a `Feature_Interface` or `Abstract_Feature` | Five classes with an identical three-method contract do not need a formal interface at this scale. Adding one creates maintenance overhead and zero runtime benefit. |
| PHP namespaces | Namespacing is valuable for large plugins or those sharing codebases. For a flat five-feature plugin with a consistent `Responsive_Goodies_` prefix, PSR-4 namespacing + autoloading is a bigger change than the refactor warrants. |
| PSR-4 autoloading / Composer | Same reason. The explicit `require_once` chain in `load_dependencies()` is visible, debuggable, and sufficient. |
| Dependency injection for options | Passing the options array as a constructor parameter rather than reading from `get_option()` inside the constructor would require orchestrator changes and produces no tangible benefit. |
| Splitting `responsive_goodies_options` | One DB read is better than five. |
| Adding a service container | Gross over-engineering for this scale. |
| Extracting an abstract base class | A shared `is_enabled()` implementation would save ~3 lines per feature class. Not worth the inheritance complexity. |
| Nonce verification | Explicitly out of scope (see PROJECT.md). |
| Full PHPDoc coverage | Explicitly out of scope. Document non-obvious methods only. |

---

## Build Order

Changes must be applied in this sequence to avoid chasing type errors introduced mid-refactor:

1. **Naming consistency pass** (QUAL-01, QUAL-02, QUAL-03) — establish the correct names before typed signatures lock them in.
2. **Dead code removal** (QUAL-05) — including the `frontend.js` stub decision — so there is less code to type-annotate.
3. **Typed properties** — once naming is stable and dead code is gone.
4. **Return type declarations** — depends on typed properties being correct.
5. **`match` expressions** — once sanitise/option logic is stable.
6. **Null-safe / nullsafe operator and `??` consistency** — cosmetic; can be done alongside step 4 or 5.
7. **Conditional asset loading** (QUAL-07) — last, because it may touch enqueue hooks; do after feature classes are stable.
8. **PHPDoc gaps** (QUAL-08) — always last; documents the final state.

---

## Component Boundaries (Current — Preserve)

| Component | Responsibility | Communicates With |
|-----------|---------------|-------------------|
| Entry point (`responsive-goodies.php`) | Constants, bootstrap, activation hooks | Orchestrator only |
| Orchestrator | Load deps, instantiate features, check Divi state, enqueue shared assets | All feature classes, Settings, Entry point |
| Settings (`class-settings.php`) | Admin UI, Settings API registration, input sanitisation | WordPress Settings API, Options API |
| Feature classes (×5) | Read own flag, register own hooks when enabled, enqueue own assets | WordPress hooks, Options API |
| Updater / Changelog | GitHub API integration, WP update transient | `wp_remote_get`, WordPress updater filters |

The boundary between orchestrator and feature classes is the most important to preserve: the orchestrator must not know about feature-specific logic, and features must not know about each other.

---

## Confidence Notes

| Area | Confidence | Basis |
|------|------------|-------|
| Keep/don't-touch list | HIGH | Derived from codebase analysis |
| PHP 8.x typed properties, return types | HIGH | PHP official docs, php.watch |
| Constructor promotion applicability | HIGH | Pattern doesn't fit feature constructors; confirmed against constructor promotion RFC |
| `match` expression guidance | HIGH | PHP 8.0 official docs |
| Over-engineering assessment | MEDIUM | Based on plugin scale and WordPress conventions; reasonable engineers may disagree on interface extraction |

---

*Research: 2026-05-14*
