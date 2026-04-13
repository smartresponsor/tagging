# ADR: Symfony-native app alignment for Tagging

## Status

Accepted for the active sanitization branch.

## Context

The Tagging repository currently combines:

- canonical business/application/infrastructure code under `src/`
- a hand-built runtime adapter under `host-minimal/`
- runtime-centric configuration and documentation that grew around that adapter

This shape allowed the component to be runnable as a compact hosted slice, but it no longer matches the desired ecosystem direction.

The target direction for Tagging is a normal Symfony-native, ecosystem-aligned application/component surface that:

- composes through standard Symfony service wiring instead of a custom bootstrap composition root
- fits naturally into monorepo and Composer-based integration paths
- does not preserve `host-minimal` as the primary runtime path
- removes transitional custom-runtime complexity after migration rather than carrying it forward as legacy

## Decision

Tagging is migrated toward a Symfony-native application alignment.

`host-minimal` is no longer treated as the strategic runtime direction for the repository.

The migration is executed as a sanitization/refactor track with the following target outcomes:

1. establish a Symfony-native composition root
2. move runtime wiring from `host-minimal/bootstrap.php` into standard Symfony service definitions
3. normalize HTTP/controller/route runtime assembly around Symfony-native patterns
4. evacuate custom bootstrap/runtime glue from the mainline runtime path
5. clean repository documentation, audits, and release/readiness artifacts so that the old runtime model does not remain as active truth

## Scope

### In scope

- Symfony-native service composition
- service alias minimization to only real dependency-inversion seams
- controller/service wiring normalization
- runtime surface cleanup
- route/runtime documentation cleanup
- audit and release-surface cleanup related to the old runtime boundary

### Out of scope

- preserving `host-minimal` as an equal alternative runtime
- keeping legacy bootstrap pathways for backward compatibility during pre-RC development
- adding new product/runtime features before composition cleanup is complete

## Planned execution waves

### Wave 1

Create the Symfony-native composition blueprint and identify the new canonical runtime entry surface.

### Wave 2

Move service and repository wiring from manual bootstrap assembly into Symfony-native service registration.

### Wave 3

Normalize HTTP/controller/middleware/route composition around the Symfony runtime surface.

### Wave 4

Remove or demote `host-minimal` from the primary runtime path.

### Wave 5

Clean docs, audits, config notes, and release/readiness artifacts that still describe the old runtime model as active truth.

### Wave 6

Run final repository sanitation so the resulting surface is clean, coherent, and free of transitional runtime drift.

## Guardrails

- keep domain and application semantics in `src/`
- avoid introducing a second custom runtime abstraction while removing the first one
- keep only genuinely useful interface-to-implementation declarations
- prefer Symfony-native defaults over bespoke runtime conventions
- treat stale runtime references as cleanup debt, not as compatibility requirements

## Consequences

### Positive

- Tagging becomes easier to integrate into the broader Symfony-oriented ecosystem
- service wiring becomes more transparent and more maintainable
- runtime and documentation truth become aligned again
- RC hardening can target the intended runtime model instead of a transitional adapter

### Trade-offs

- the repository will undergo multi-wave composition refactoring
- some existing docs, audits, and bootstrap assumptions will need coordinated cleanup
- short-term migration effort increases in exchange for lower long-term maintenance drag
