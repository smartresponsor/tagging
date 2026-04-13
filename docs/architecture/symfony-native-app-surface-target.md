# Symfony-native app surface target

## Purpose

This document enumerates the normal Symfony-native application surface that is currently missing from Tagging and that must be introduced during the sanitization migration.

The repository currently has no standard Symfony application bootstrap files such as a kernel, bundle registration, service configuration, or route configuration. Instead, runtime assembly is delegated to the custom `host-minimal` path.

## Missing Symfony-native surface

The following file families are currently absent and must be introduced or deliberately replaced with equivalent Symfony-native structures:

### Runtime/bootstrap

- `public/index.php` as a Symfony front controller
- `bin/console`
- `config/bootstrap.php`
- `src/Kernel.php`

### Framework configuration

- `config/packages/framework.yaml`
- `config/packages/routing.yaml` or equivalent route-loading configuration
- `config/packages/cache.yaml` where runtime cache semantics need normal Symfony handling
- `config/services.yaml`
- environment-specific config files only if justified

### Routing surface

- `config/routes.yaml` or a route import tree under `config/routes/`
- route mapping aligned with controller service wiring rather than custom host dispatch

### Service composition

- standard Symfony service discovery/import rules under `config/services.yaml`
- explicit aliases only for real interface-to-implementation seams
- removal of custom composition responsibility from `host-minimal/bootstrap.php`

## Migration intent by file zone

### Files to introduce

- `src/Kernel.php`
- `config/bootstrap.php`
- `config/services.yaml`
- `config/routes.yaml`
- `bin/console`
- supporting package configuration files required for a minimal Symfony-native runtime

### Files to transition away from primary truth

- `host-minimal/index.php`
- `host-minimal/bootstrap.php`
- runtime docs that declare `host-minimal` as the active intended runtime
- config truth that encodes `runtime: host-minimal` as the strategic target

### Files that may remain as derived truth if still useful

- route catalogs
n- public surface projections
- release/openapi artifacts

These may remain only if they are projections from the Symfony-native runtime truth and not a competing bootstrap model.

## Acceptance signal for the next wave

The next implementation wave is ready when the repository has a defined target app surface and the missing Symfony-native bootstrap/config files can be added as the new canonical runtime baseline.
