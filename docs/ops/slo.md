# Tag Observability & SLO Gate (RC5-E5)

**Metrics (A2 exporter expected)**:
- `tag_http_requests_total{route,method,status,tenant}`
- `tag_http_request_duration_seconds_bucket{route,method,tenant,le}` (Prometheus histogram)
- `tag_cache_hits_total{cache,tenant}`, `tag_cache_misses_total{cache,tenant}`
- `tag_quota_rate_limit_exceeded_total`

**SLO targets (RC5)**:
- Read p95 ≤ 0.25s (search/get)
- Error rate ≤ 0.5% (4xx except validation may be tracked separately)
- Presence endpoints healthy: `/tag/_status`

**Usage**:
- Import `ops/grafana/tag-dashboard.json` to Grafana.
- Load `ops/alerts/tag-alerts.yaml` in Prometheus/Alertmanager.
- Run GitHub Action **tag-slo-gate** or local script:
  ```bash
  BASE_URL=http://localhost:8080 TENANT=demo ./tools/synthetic/tag-slo-gate.sh
  ```

Generated: 2025-10-27T20:51:56.441751
