# E25 Observability+

Current runtime observability for the shipped Tag slice is centered on middleware-based request observation, not on a published metrics HTTP endpoint.

## Active runtime observation

- `Observe` wraps live host-minimal dispatch.
- `_status` remains the minimal health/readiness route.
- `_surface` remains the discovery route for the public shell.

## Signals

- request latency and error classification are recorded through the observe middleware path
- slow requests can be written to `report/tag/slowlog.ndjson`
- config lives in `config/tag_observability.yaml`

## Current posture

- there is **no shipped `/tag/_metrics` route** in the current public shell
- observability is currently middleware/file/config driven, not Prometheus-endpoint driven
- unpublished internal webhook routes are not part of the public shell

## Host-minimal cleanup

- host-minimal now wraps dispatch through `Observe`, so latency/error metrics and slowlog recording apply on live requests, not only in docs
- the composition root exports `observeMiddleware` explicitly for isolated testing

## Operational use

- use `GET /tag/_status` for lightweight runtime liveness/readiness checks
- use `GET /tag/_surface` to verify the current shipped public surface before smoke or manual checks
- collect slowlog output from `report/tag/slowlog.ndjson` when enabled
- treat a dedicated metrics endpoint as a future enhancement, not as a currently shipped contract
