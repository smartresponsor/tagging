# Tag RC2 Upgrade Guide
## What changed
- OpenAPI bumped to v1.1.0 and includes endpoints for merge/split, bulk import, redirect resolve, metrics, webhooks.
- E14..E19 hardening, policy gate, UI v2, metrics/SLO, cache/quota, audit/webhooks.

## Compatibility
- No breaking changes vs RC1 contracts; new endpoints are additive.
- SDK TS/PHP minor marker updated to 1.1.0.

## Steps
1) Apply DB hardening SQLs from E14 (engine-specific).
2) Deploy updated service and static Admin UI (E16).
3) Verify /tag/_metrics and run tools/smoke/tag_smoke.sh.
4) (Optional) configure cache/quota/webhooks via config/*.yaml.
