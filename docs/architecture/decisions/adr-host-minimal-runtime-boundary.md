# ADR: host-minimal runtime boundary for Tagging

## Status

Accepted for the current shipped slice.

## Context

The Tagging repository currently ships a runnable `host-minimal` runtime alongside the canonical library code under `src/`.

This runtime is intentionally compact, but it still carries important production-shaping responsibilities:

- runtime configuration and environment resolution
- route dispatch over the canonical route catalog
- security middleware application boundaries
- status and discovery endpoints
- controller/service composition

Without an explicit boundary statement, `host-minimal` can gradually accumulate responsibilities that belong either in canonical application code or in delivery-only runtime glue.

## Decision

`host-minimal` is treated as the shipped runtime adapter for the current Tagging slice, not as a second source of domain truth.

The following are canonical sources of truth:

- `src/` for business/application/infrastructure code
- `tag.yaml` for route truth
- `contracts/http/tag-openapi.yaml` for shipped HTTP contract expectations

`host-minimal` may compose and expose those truths, but it must not silently redefine them.

## Guardrails

- keep business semantics out of `host-minimal` when they belong in `src/`
- keep route truth centralized in `tag.yaml`
- keep public health/discovery endpoints explicit in runtime config and tests
- prefer additive tests around runtime composition before broadening middleware or config semantics

## Consequences

### Positive

- runtime composition remains explicit and testable
- the shipped adapter can stay compact without pretending to be the canonical application layer
- RC hardening can focus on composition discipline instead of inventing duplicate domain logic

### Trade-offs

- some operational behavior still lives in a hand-built runtime adapter
- future migration to a fuller Symfony runtime will require an intentional transition rather than a silent drift
