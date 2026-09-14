# HTTP Middleware Order

Recommended chain:

1) TagObserveMiddleware (latency/error/slowlog)
2) TagVerifySignatureMiddleware (HMAC/nonce/timestamp)
3) TagTenantContextMiddleware (inject tenantId)
4) TagAuthorizeMiddleware (roles/gate)
5) Handler
