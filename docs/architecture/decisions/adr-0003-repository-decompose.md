# ADR-0003: Repository interface is decomposed by capability

Status: Accepted

## Context

A single broad repository interface aggregated unrelated concerns (CRUD + policy + moderation + analytics + effects).
This violates interface segregation and increases coupling.

## Decision

Replace the god-interface with cohesive interfaces:

- TagWriteRepositoryInterface
- TagReadRepositoryInterface
- TagPolicyRepositoryInterface (or engine interface if not persistence)
- Optional: moderation/analytics interfaces as the surface grows

A compatibility adapter may exist temporarily to preserve old call-sites during migration.

## Consequences

- Cleaner boundaries and testability.
- Easier host/framework integration.
- Temporary adapter complexity during transition.
