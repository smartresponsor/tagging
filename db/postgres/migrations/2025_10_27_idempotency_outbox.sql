-- RC5-E3: Idempotency + Outbox tables
-- Generated: 2025-10-27T20:29:49.107860

CREATE TABLE IF NOT EXISTS idempotency_store
(
    tenant      TEXT        NOT NULL,
    key         TEXT        NOT NULL,                   -- X-Idempotency-Key
    op          TEXT        NOT NULL,                   -- semantic operation code (e.g., tag.assign)
    checksum    TEXT        NOT NULL,                   -- hash of request payload (optional enforcement)
    status      TEXT        NOT NULL DEFAULT 'pending', -- pending|done|failed
    result_json JSONB,                                  -- last known result (response skeleton)
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, key)
);

CREATE OR REPLACE FUNCTION idempotency_set_updated_at()
    RETURNS TRIGGER AS
$$
BEGIN
    NEW.updated_at = now(); RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS idempotency_store_set_updated_at ON idempotency_store;
CREATE TRIGGER idempotency_store_set_updated_at
    BEFORE UPDATE
    ON idempotency_store
    FOR EACH ROW
EXECUTE FUNCTION idempotency_set_updated_at();

CREATE TABLE IF NOT EXISTS outbox_event
(
    id           BIGSERIAL PRIMARY KEY,
    tenant       TEXT        NOT NULL,
    topic        TEXT        NOT NULL, -- e.g., tag.assigned
    payload      JSONB       NOT NULL,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
    delivered_at TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS outbox_event_tenant_created_idx
    ON outbox_event (tenant, created_at);
