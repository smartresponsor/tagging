# Signature + middleware contract

The host-minimal entrypoint now applies transport middleware through a small explicit pipeline.

## Active middleware order

1. `TagObserveMiddleware`
2. `TagVerifySignatureMiddleware`
3. route dispatch

## TagVerifySignatureMiddleware contract

When `TAG_SIGNATURE_SECRET` is configured, write and non-meta `/tag/**` routes require:

- `X-SR-Timestamp`
- `X-SR-Nonce`
- `X-SR-Signature`

Failure responses are JSON and `no-store`:

```json
{"ok":false,"code":"signature_missing"}
```

401 responses also include `WWW-Authenticate: HMAC-SHA256`.

Meta routes such as `/tag/_status` and `/tag/_surface` remain excluded.
