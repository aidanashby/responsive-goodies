# Technology Stack — PHP 8.x / WP 6.x Refactor Standards

**Project:** Responsive Goodies v0.3.8 refactor
**Researched:** 2026-05-14
**Scope:** What PHP 8.x and WordPress 6.x features to adopt; what to avoid

---

## PHP 8.x Features to Adopt

### 1. Typed Properties (PHP 8.0+)
**Adopt: Yes. Confidence: HIGH**

Add type declarations to class properties. Typed properties are required before `readonly` can be used, and they eliminate a class of null/type bugs at runtime rather than at call time.

```php
// Before
private $options;
private $version;

// After
private array $options;
private string $version;
```

Apply to all five feature classes and the orchestrator. Use `?string` / `?array` nullable types where a property may legitimately be null before initialisation.

---

### 2. Match Expressions (PHP 8.0+)
**Adopt: Where it replaces switch with a return value. Confidence: HIGH**

`match` is strict (no type coercion), returns a value, and has no fall-through. Use it wherever a `switch` or `if/elseif` chain selects among a closed set of values — e.g., mapping option keys to CSS selectors or hook names.

```php
// Before
switch ( $device ) {
    case 'mobile': $class = 'is-mobile'; break;
    case 'tablet': $class = 'is-tablet'; break;
    default:       $class = 'is-desktop';
}

// After
$class = match( $device ) {
    'mobile' => 'is-mobile',
    'tablet' => 'is-tablet',
    default  => 'is-desktop',
};
```

Do not retrofit every `if` block. Reserve for closed-set value mapping.

---

### 3. Nullsafe Operator (PHP 8.0+)
**Adopt: Yes, for WP object chains. Confidence: HIGH**

The `?->` operator short-circuits to `null` if the left side is null, preventing fatal errors on potentially-null WP objects (e.g., post objects, user objects, `WP_Screen`).

```php
// Before
$screen = get_current_screen();
$base   = $screen ? $screen->base : null;

// After
$base = get_current_screen()?->base;
```

Apply wherever plugin code calls methods on objects returned by WP functions that can return `null` (e.g., `get_current_screen()`, `wp_get_current_user()`).

---

### 4. Named Arguments (PHP 8.0+)
**Adopt: Sparingly. Confidence: MEDIUM**

Named arguments improve readability on WP functions with long positional signatures. Use them at call sites where positional order is non-obvious — the main candidate in this codebase is `wp_enqueue_script` / `wp_enqueue_style`.

```php
wp_enqueue_script(
    handle:   'responsive-goodies-frontend',
    src:      plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js',
    deps:     [ 'jquery' ],
    ver:      RG_VERSION,
    args:     [ 'strategy' => 'defer' ],
);
```

Do not apply globally — only where the positional signature is genuinely confusing. Avoid on simple two-argument calls.

---

### 5. Readonly Properties (PHP 8.1+)
**Adopt: For constants that are set once in the constructor. Confidence: MEDIUM**

Use `readonly` on properties like plugin version, base path, and option key that are assigned in `__construct` and must not change. Requires a type declaration (which QUAL-06 will add anyway).

```php
private readonly string $version;
private readonly string $option_key;
```

Do not use `readonly` on properties that WP lifecycle hooks need to write after construction.

---

### 6. Enums (PHP 8.1+)
**Adopt: No for this refactor. Confidence: HIGH**

Enums would be a meaningful abstraction for feature names (orphan-fix, device-menu, etc.), but introducing them is a structural change beyond the quality-pass scope. Flag as a candidate for a future refactor milestone.

---

### 7. Fibers (PHP 8.1+)
**Adopt: No. Confidence: HIGH**

Fibers are for cooperative concurrency. WordPress plugins execute synchronously in a request lifecycle. No applicable use case here.

---

### 8. First-Class Callable Syntax (PHP 8.1+)
**Adopt: Conditionally. Confidence: MEDIUM**

`Closure::fromCallable([$this, 'method'])` can be replaced with `$this->method(...)`. Useful if hooks are registered using closures or callable arrays.

```php
// Before
add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

// After — only if refactoring hook registration style
add_action( 'wp_enqueue_scripts', $this->enqueue_assets(...) );
```

Keep existing `[$this, 'method']` hook registration style unless there is a specific reason to change — it is widely understood by WP developers.

---

## WordPress 6.x APIs to Apply

### Script Loading Strategy (WP 6.3+)
**Adopt: Yes. Directly addresses QUAL-07. Confidence: HIGH**

As of WP 6.3, `wp_enqueue_script` accepts an `$args` array as its fifth parameter (replacing the boolean `$in_footer`). The `strategy` key accepts `'defer'` or `'async'`.

```php
wp_enqueue_script(
    'responsive-goodies-frontend',
    plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js',
    [ 'jquery' ],
    RG_VERSION,
    [ 'strategy' => 'defer' ]
);
```

Use `defer` (not `async`) for frontend scripts that depend on jQuery — defer preserves execution order, async does not.

**Caveat:** If any inline script is attached to the handle in the `after` position, WP will silently strip the strategy and fall back to blocking. Verify no `wp_add_inline_script` calls target these handles in `after` position.

---

### Conditional Enqueuing
**Adopt: Verify and enforce. Confidence: HIGH**

Each feature class should check a conditional before enqueueing — only load assets when the feature is active and the context is appropriate (e.g., not in wp-admin for frontend scripts). The Divi builder check (`et_fb_is_enabled()`) is already present; ensure it wraps all enqueue calls, not just some.

---

### Settings API
**Adopt: Retain existing pattern. Confidence: HIGH**

The plugin already uses the WP Settings API (`register_setting`, `add_settings_section`, `add_settings_field`). No change needed for the refactor. Ensure `sanitize_callback` is registered for `responsive_goodies_options` if not already set — this is the QUAL-04 check.

---

### `wp_admin_notice()` (WP 6.4+)
**Adopt: Consider for admin notices. Confidence: MEDIUM**

WP 6.4 introduced `wp_admin_notice()` as a standardised replacement for echoing `<div class="notice ...">` manually. If the plugin outputs any admin notices (e.g., on settings save), migrate to this function.

---

## WordPress Coding Standards (WPCS 3.x)

WPCS 3.x targets WP 6.2+ as its minimum baseline. The rules most relevant to this codebase:

| Rule category | What it enforces | Apply |
|---|---|---|
| `WordPress.Security.EscapeOutput` | All echo/print output escaped with `esc_html`, `esc_attr`, `wp_kses` etc. | Verify on settings page output |
| `WordPress.Security.ValidatedSanitizedInput` | `$_POST`/`$_GET` inputs sanitised before use | Verify on options save |
| `WordPress.DateTime.CurrentTimeTimestamp` | Disallows `current_time('timestamp')` — use `time()` or `current_datetime()` | Check if used anywhere |
| `WordPress.PHP.DisallowShortTernary` | Disallows `?:` short ternary | Replace any instances |
| `WordPress.NamingConventions.ValidHookName` | Hook names lowercase, words separated by underscores, plugin-prefixed | Directly serves QUAL-02 |
| `WordPress.NamingConventions.PrefixAllGlobals` | All global functions, classes, constants prefixed | Verify prefix consistency for QUAL-01/QUAL-03 |
| Yoda conditions | Comparison literals on the left (`if ( 'value' === $var )`) | Apply throughout |

Install WPCS locally as a dev dependency to run automated checks:

```bash
composer require --dev wp-coding-standards/wpcs:"^3.0"
```

There is currently no Composer setup in this project. For a no-build-pipeline codebase, running PHPCS manually before commit is sufficient — no CI integration needed for this pass.

---

## What NOT to Use

| Feature | Reason |
|---|---|
| PHP 8.1 Enums | Structural change; out of scope for a quality pass |
| Fibers | No concurrency model in WP plugin lifecycle |
| `async` script strategy | Breaks jQuery-dependent scripts (execution order not guaranteed) |
| Named args on all WP function calls | Over-engineering; apply only where positional order is genuinely unclear |
| `match` as a drop-in for all `if` blocks | Match requires exhaustive arms or a `default`; do not introduce it where an `if` is already readable |
| `readonly` on properties written by WP hooks post-construction | Will cause a fatal — WP lifecycle writes happen after `__construct` |

---

## Confidence Assessment

| Area | Confidence | Basis |
|---|---|---|
| PHP 8.0 typed props, match, nullsafe | HIGH | PHP.Watch official docs, PHP.net releases |
| PHP 8.1 readonly, enums | HIGH | PHP.net official release notes |
| WP 6.3 script strategy API | HIGH | Official Make WordPress Core post |
| WP 6.4 `wp_admin_notice()` | MEDIUM | Official WP dev blog; not yet verified against this codebase's actual admin notice usage |
| WPCS 3.x sniff rules | HIGH | Official WPCS GitHub releases, WordPress Coding Standards handbook |
| Namespaces recommendation | MEDIUM | Official WP dev blog (Sept 2025); excluded from this refactor as structural change |

---

## Sources

- [PHP 8.0 Nullsafe Operator — PHP.Watch](https://php.watch/versions/8.0/null-safe-operator)
- [PHP 8.1 Readonly Properties — PHP.Watch](https://php.watch/versions/8.1/readonly)
- [PHP 8.1 Enums — PHP.Watch](https://php.watch/versions/8.1/enums)
- [Registering scripts with async and defer — Make WordPress Core (WP 6.3)](https://make.wordpress.org/core/2023/07/14/registering-scripts-with-async-and-defer-attributes-in-wordpress-6-3/)
- [WordPress Coding Standards — Developer.WordPress.org](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- [WordPress/WordPress-Coding-Standards releases — GitHub](https://github.com/WordPress/WordPress-Coding-Standards/releases)
- [Implementing namespaces and coding standards in WordPress plugin development — WordPress Developer Blog](https://developer.wordpress.org/news/2025/09/implementing-namespaces-and-coding-standards-in-wordpress-plugin-development/)
