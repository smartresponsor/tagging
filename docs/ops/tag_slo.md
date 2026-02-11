# Tag SLO (E17)

Targets:

- Read p95 ≤ 250 ms
- Write p95 ≤ 700 ms
- Error-rate ≤ 0.5% (rolling 1h)

Measure:

- Use nginx upstream_time or app timings; export as Prometheus counters/summaries via TagMetrics.
- Track counters: tag_request_total{op=read|write,code}, tag_error_total{op}, tag_quota_exceeded_total.
- Track summary: tag_request_latency_seconds{op}.
- Gate: CI smoke checks status + basic latency (local), prod gates read from Prometheus (out-of-scope here).
