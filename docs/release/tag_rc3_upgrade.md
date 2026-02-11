# Tag RC3 Upgrade Guide

## Summary

- Contracts bumped to OpenAPI v1.2.0 (additive): status, metrics, webhooks, merge/split, bulk, redirect.
- Security v2 (nonce/timestamp), Role-gate minimal, Webhooks retry/backoff, Observability+.

## Compatibility

- No breaking changes vs RC2; all changes additive.
- SDK markers set to 1.2.0 when SDKs are present.

## Steps

1) Deploy app code (E21..E25) → confirm /tag/_status 200.
2) Verify /tag/_metrics exports latency/error metrics.
3) Configure role gate (fallback_allow_all=false when Role ready).
4) Run webhook worker (cron/systemd) and verify DLQ empty on healthy endpoints.
