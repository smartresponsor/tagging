# Existing file rewrite map for Symfony-native alignment

## Purpose

This map identifies the already-existing files that must be rewritten in place during the next mutation-capable patch wave.

These files currently keep `host-minimal` as active runtime truth or keep the old runtime path as the effective public entry.

## High-priority in-place rewrites

### 1. `composer.json`

#### Current issue

The repository has no Symfony-native runtime dependency baseline.

#### Required rewrite

Add the minimum runtime packages needed for the introduced Symfony-native baseline surface, including framework, kernel, DI, routing, console, config, dotenv/yaml support as justified.

### 2. `public/index.php`

#### Current issue

The public entry delegates directly to `host-minimal/index.php`.

#### Required rewrite

Replace delegation to `host-minimal/index.php` with the Symfony-native front controller path after runtime dependencies are in place.

### 3. `tag.yaml`

#### Current issue

The route truth still declares `runtime: host-minimal`.

#### Required rewrite

Remove `host-minimal` as the strategic runtime declaration and align runtime metadata with the Symfony-native target path while preserving valid route truth.

### 4. `config/tag_runtime.php`

#### Current issue

The runtime metadata projection still defaults runtime to `host-minimal` and projects the old runtime story.

#### Required rewrite

Update runtime metadata defaults and comments so they describe transitional or Symfony-native truth rather than the old runtime as active target.

### 5. `docs/http/http-wiring.md`

#### Current issue

The document still describes the shipped HTTP runtime as `host-minimal`.

#### Required rewrite

Rewrite the document so `host-minimal` is treated as migration debt or historical context rather than the intended runtime model.

## Medium-priority in-place rewrites

### 6. `docs/architecture/decisions/adr-host-minimal-runtime-boundary.md`

Retain as historical decision context only, or downgrade its authority so it no longer conflicts with the Symfony-native alignment ADR.

### 7. runtime/ops/install docs that still describe `host-minimal` as current truth

Audit and rewrite all such references after the runtime entry changes are in place.

## Execution note

These rewrites are intentionally separated from the file-creation wave because the current connector workflow used in this lane is better suited to additive commits than direct in-place mutation of existing files.

The next mutation-capable patch wave should batch these rewrites together to prevent partial runtime-truth drift.
