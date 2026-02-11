# HTTP Middleware Order

Recommended chain:

1) Observe (latency/error/slowlog)
2) VerifySignature (HMAC/nonce/timestamp)
3) TenantContext (inject tenantId)
4) Authorize (roles/gate)
5) Handler
