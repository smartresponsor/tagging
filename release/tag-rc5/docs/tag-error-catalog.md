# Error Catalog (code → meaning)

invalid_tenant       : Missing/invalid X-Tenant-Id
unauthorized         : Auth required or bad signature (A1)
forbidden            : Caller lacks permission
not_found            : Resource not found
conflict             : Unique constraint / assignment duplication
rate_limit_global    : Global route rate limit exceeded
rate_limit_tenant    : Tenant route rate limit exceeded
quota_soft_exceeded  : Soft per-minute quota exceeded
validation_failed    : Request validation failed
internal_error       : Unexpected server error
