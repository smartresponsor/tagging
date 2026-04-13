# Symfony-native Wave 1 blueprint

## Purpose

This blueprint defines the first migration target for moving Tagging away from the custom `host-minimal` runtime path and toward a normal Symfony-native runtime surface.

Wave 1 does **not** complete the migration. It establishes the new canonical runtime direction and the file-level transition map that later waves will implement.

## Current state snapshot

### Current runtime entry

- `public/index.php` delegates directly to `host-minimal/index.php`
- `tag.yaml` declares `runtime: host-minimal`
- `config/tag_runtime.php` projects public/runtime metadata from the current host-minimal-oriented surface
- `docs/http/http-wiring.md` still describes the shipped HTTP runtime as `host-minimal`

### Current composition model

- `host-minimal/bootstrap.php` acts as the active composition root
- controllers, repositories, middleware, and use cases are manually assembled there
- the repository therefore behaves as a hybrid library plus custom runtime adapter

## Wave 1 target

Establish the Symfony-native runtime model as the intended primary runtime path.

This requires:

1. declaring the Symfony-native runtime direction in architecture and runtime docs
2. identifying the future canonical runtime entry files
3. distinguishing runtime truth that remains valid from runtime glue that must be evacuated
4. preparing for service wiring migration without yet deleting stable domain code

## Canonical target runtime surface

### Primary runtime path

- `public/index.php` should become a Symfony-native front controller path
- service composition should move into standard Symfony service registration
- route/controller wiring should align with Symfony-native conventions

### Transition rule

Until migration completion, `host-minimal/` is considered transitional migration debt, not strategic runtime truth.

## File-zone transition map

### Runtime entry and composition

#### Current

- `public/index.php`
- `host-minimal/index.php`
- `host-minimal/bootstrap.php`

#### Target direction

- `public/index.php` becomes Symfony-native
- `host-minimal/index.php` and `host-minimal/bootstrap.php` are removed or demoted from the primary runtime path in later waves

### Runtime truth and route metadata

#### Current

- `tag.yaml`
- `config/tag_runtime.php`
- `config/tag_public_surface.php`
- `config/tag_route_catalog.php`

#### Target direction

- preserve route truth where valid
- remove active runtime truth that encodes `host-minimal` as the intended runtime model
- keep derived/public-surface artifacts only if they remain compatible with Symfony-native runtime semantics

### HTTP runtime documentation

#### Current

- `docs/http/http-wiring.md`
- `docs/api/status-surface.md`
- operational/runtime docs that still describe host-minimal behavior

#### Target direction

- rewrite runtime docs to describe the Symfony-native runtime path
- treat host-minimal descriptions as stale unless explicitly retained as historical notes

### Service composition and DI

#### Current

- manual assembly in `host-minimal/bootstrap.php`
- use case and repository contracts partly bypassed by direct `new ...` assembly

#### Target direction

- move to Symfony-native service registration
- keep only genuinely useful interface-to-implementation bindings
- avoid recreating a second bespoke runtime abstraction

## Expected Wave 2 prerequisites created by Wave 1

Wave 1 is considered complete when the repository has a clear file-level migration map and the Symfony-native direction is documented as the intended runtime truth.

Wave 2 will then move actual composition wiring.

## Risks

- documentation drift if old host-minimal runtime docs remain active truth
- temporary duality while new runtime direction exists before full service migration
- over-preserving compatibility paths that should instead be evacuated during pre-RC sanitation

## Guardrails

- do not move business semantics out of `src/`
- do not invent a new custom adapter while removing the old one
- prefer Symfony-native defaults over special runtime rules
- treat pre-RC migration as sanitation, not backward-compatibility preservation
