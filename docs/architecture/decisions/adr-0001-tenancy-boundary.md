# ADR-0001: Tenant boundary is explicit everywhere

Status: Accepted

## Context

The schema is tenant-centric, but some service/repository methods did not require tenant in the contract. This makes cross-tenant operations possible by mistake.

## Decision

All boundary methods that can read or write tenant-owned data MUST require tenant explicitly:

- Repository interfaces require tenant argument/value object.
- Application use-cases require tenant in command DTOs.
- HTTP layer extracts tenant from request context and passes it down.

## Consequences

- More verbose signatures.
- Migration cost to update call-sites.
- Correctness wall against cross-tenant data leaks.
