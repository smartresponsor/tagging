# E25 Observability+

Current runtime observability for the shipped Tag slice is centered on middleware-based request observation, not on a published metrics HTTP endpoint.

## Active runtime observation

- `TagObserveMiddleware` remains the Tagging request-observation middleware available to the host composition.
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

## Hosted composition

- the Symfony host composes Tagging middleware and services through the package container wiring
- Tagging owns the observation behavior while the host owns the front controller and top-level request lifecycle

## Operational use

- use `GET /tag/_status` for lightweight runtime liveness/readiness checks
- use `GET /tag/_surface` to verify the current shipped public surface before smoke or manual checks
- collect slowlog output from `report/tag/slowlog.ndjson` when enabled
- treat a dedicated metrics endpoint as a future enhancement, not as a currently shipped contract
