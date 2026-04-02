# ADR: public shell scope for Tagging

## Status

Accepted for the current shipped slice.

## Context

The Tagging repository contains runtime code, operational helpers, demo materials, SDK examples, and internal webhook-related capabilities.

Without an explicit scope statement, future waves can accidentally blur the line between:

- what is publicly shipped and supported now
- what exists only as internal or operational capability
- what is planned but not part of the current contract

## Decision

The current public shell is the route set intentionally exposed through the canonical route truth and public surface projection.

Public-shell confidence must be grounded in:

- `tag.yaml`
- `config/tag_public_surface.php`
- `contracts/http/tag-openapi.yaml`
- runtime smoke and truth tests

Private webhook-management operations are not part of the current public shell, even if they exist in runtime/catalog form.

## Guardrails

- do not advertise private webhook-management routes as public API
- treat `_status` and `_surface` as public operational endpoints for the shipped slice
- treat bulk assignment routes as part of the current public shell
- do not let docs or examples promise unpublished routes or operational surfaces

## Consequences

### Positive

- contract discussions stay focused on the actually shipped slice
- smoke and release checks can validate the public shell deterministically
- docs can distinguish between public support and internal capability

### Trade-offs

- some runtime capabilities remain intentionally non-public
- future public-surface expansion should be explicit and test-backed rather than implied by code presence alone
