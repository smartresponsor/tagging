# ADR: canonical route truth centralization for Tagging

## Status

Accepted for the current shipped Tagging slice.

## Context

The Tagging component exposes a public shell that must stay synchronized across several delivery surfaces:

- runtime dispatch in `host-minimal/route.php`
- public surface discovery in `config/tag_public_surface.php`
- contract and release documentation
- route/surface/contract audits

Historically, the main risk in this area is silent drift: one route list is updated while another is left behind.

## Decision

`tag.yaml` is the canonical route truth for the shipped Tagging runtime.

The repository projects that route truth into runtime and delivery surfaces instead of maintaining multiple handwritten route lists.

The current projection chain is:

1. `tag.yaml`
2. `config/tag_route_catalog.php`
3. `host-minimal/route.php`
4. `config/tag_public_surface.php`
5. route/surface/contract tests and audits

## Consequences

### Positive

- public and non-public operations are declared once
- status and discovery response-header behavior is kept visible in the canonical catalog
- runtime route drift becomes easier to detect with focused tests
- release docs can reference one canonical route source instead of restating route tables manually

### Trade-offs

- projection code becomes critical infrastructure and must stay simple and test-backed
- the canonical catalog format must remain intentionally narrow while it is parsed by lightweight repository code
- future route metadata expansion should be introduced carefully to avoid accidental parser fragility

## Guardrails

- do not add parallel handwritten route maps as new sources of truth
- keep private webhook operations out of the public surface projection
- keep response-header metadata for `status` and `discovery` visible in the canonical catalog
- extend route truth tests before broadening catalog semantics
