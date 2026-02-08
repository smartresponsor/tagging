# ADR-0002: Outbox and idempotency are mandatory for writes

Status: Accepted

## Context

Some write flows had outbox/idempotency, others did not. Inconsistent behavior leads to duplicate side effects and difficult retries.

## Decision

- Every write endpoint MUST define idempotency requirements.
- Every write use-case MUST:
  - execute in an explicit transaction boundary
  - record idempotency (when applicable)
  - emit domain events via outbox table for async effects

## Consequences

- More schema and tests.
- Improved retry safety and auditability.
- Predictable behavior under concurrency and retries.
