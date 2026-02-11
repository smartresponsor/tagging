-- Tag component schema (Postgres) — RC5-E2
-- Generated: 2025-10-27T20:27:58.490715

CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- tenants are isolated by 'tenant' column. ULID is stored as CHAR(26).
CREATE TABLE IF NOT EXISTS tag_entity
(
    id         CHAR(26)    NOT NULL,
    tenant     TEXT        NOT NULL,
    slug       TEXT        NOT NULL,
    name       TEXT        NOT NULL,
    locale     TEXT,
    weight     INTEGER     NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, id),
    CONSTRAINT tag_entity_slug_uq UNIQUE (tenant, slug)
);

-- Any domain entity can be linked to tags
CREATE TABLE IF NOT EXISTS tag_link
(
    tenant      TEXT        NOT NULL,
    entity_type TEXT        NOT NULL CHECK (entity_type IN ('category', 'product', 'project', 'text')),
    entity_id   TEXT        NOT NULL,
    tag_id      CHAR(26)    NOT NULL,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, entity_type, entity_id, tag_id),
    CONSTRAINT tag_link_tag_fk FOREIGN KEY (tenant, tag_id)
        REFERENCES tag_entity (tenant, id) ON DELETE CASCADE
);

-- updated_at trigger
CREATE OR REPLACE FUNCTION set_updated_at()
    RETURNS TRIGGER AS
$$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS tag_entity_set_updated_at ON tag_entity;
CREATE TRIGGER tag_entity_set_updated_at
    BEFORE UPDATE
    ON tag_entity
    FOR EACH ROW
EXECUTE FUNCTION set_updated_at();
