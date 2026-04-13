# Symfony-native Wave 3 host-minimal decommission map

## Purpose

This map defines the next cleanup wave after the Wave 2 Symfony bridge is applied to `host-minimal/bootstrap.php`.

Wave 3 is focused on shrinking or removing the remaining `host-minimal` runtime path once:

- Symfony-native runtime is the primary path
- controller graphs are resolved through Symfony DI
- compatibility bridge responsibilities are clearly bounded

## Current residual host-minimal responsibilities

After the Wave 2 bridge, the remaining host-minimal path is expected to retain only transitional responsibilities such as:

- runtime metadata exposure
- default tenant fallback
- compatibility middleware pipeline
- legacy route-dispatch compatibility while old callers still use `host-minimal/index.php`

## Files in scope

### 1. `host-minimal/index.php`

#### Current role

Acts as an HTTP entry and still drives:

- CORS handling
- idempotency normalization
- middleware pipeline execution
- route dispatch through `host-minimal/route.php`

#### Wave 3 target

- demote from primary runtime entry
- keep only if explicit compatibility needs remain
- otherwise remove once no callers depend on it

### 2. `host-minimal/route.php`

#### Current role

Dispatches requests by reading `config/tag_route_catalog.php` and resolving controller services through the export container.

#### Wave 3 target

- stop treating this file as active routing truth
- keep only as a compatibility adapter if legacy runtime entry remains temporarily required
- otherwise remove when Symfony-native routing is the sole runtime truth

### 3. `host-minimal/bootstrap.php`

#### Current role after Wave 2 bridge

Provides a compatibility export surface backed by Symfony-managed services.

#### Wave 3 target

- further reduce or remove once no runtime path needs the export container shape

## Recommended Wave 3 order

### Step 1

Confirm that the primary application entry is Symfony-native and that route/controller resolution no longer depends on host-minimal.

### Step 2

Identify whether any tests, smoke scripts, or docs still invoke `host-minimal/index.php` directly.

### Step 3

If compatibility usage remains, keep host-minimal as a thin adapter only.

### Step 4

If compatibility usage no longer remains, remove:

- `host-minimal/index.php`
- `host-minimal/route.php`
- remaining host-minimal-only bootstrap/export scaffolding

## Success signal

Wave 3 is materially complete when the repository no longer needs `host-minimal` as an execution path for normal runtime behavior, and any remaining references are clearly historical or transitional.
