-- Tag component schema extension (Postgres) — E3
-- Generated: 2026-02-02
--
-- Goals:
-- - Keep Postgres migrations as the single source of truth.
-- - Close schema drift: add missing columns/tables used by services.
-- - Idempotent and safe to re-run.

CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- -----------------------------------------------------------------------------
-- Core: tag_entity
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_entity
(
    id            CHAR(26)    NOT NULL,
    tenant        TEXT        NOT NULL,
    slug          TEXT        NOT NULL,
    name          TEXT        NOT NULL,
    locale        TEXT,
    weight        INTEGER     NOT NULL DEFAULT 0,
    required_flag BOOLEAN     NOT NULL DEFAULT FALSE,
    mod_only_flag BOOLEAN     NOT NULL DEFAULT FALSE,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, id),
    CONSTRAINT tag_entity_slug_uq UNIQUE (tenant, slug)
);

DO
$$
    BEGIN
        IF NOT EXISTS (SELECT 1 as alias2
                       FROM information_schema.columns
                       WHERE table_name = 'tag_entity'
                         AND column_name = 'required_flag') THEN
            ALTER TABLE tag_entity
                ADD COLUMN required_flag BOOLEAN NOT NULL DEFAULT FALSE;
        END IF;
        IF NOT EXISTS (SELECT 1 as alias
                       FROM information_schema.columns
                       WHERE table_name = 'tag_entity'
                         AND column_name = 'mod_only_flag') THEN
            ALTER TABLE tag_entity
                ADD COLUMN mod_only_flag BOOLEAN NOT NULL DEFAULT FALSE;
        END IF;
    END
$$;

-- updated_at trigger (guarded)
CREATE OR REPLACE FUNCTION set_updated_at()
    RETURNS TRIGGER AS
$$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DO
$$
    BEGIN
        IF NOT EXISTS (SELECT 1 as alias
                       FROM pg_trigger
                       WHERE tgname = 'tag_entity_set_updated_at') THEN
            CREATE TRIGGER tag_entity_set_updated_at
                BEFORE UPDATE
                ON tag_entity
                FOR EACH ROW
            EXECUTE FUNCTION set_updated_at();
        END IF;
    END
$$;

-- Search/index hardening
CREATE INDEX IF NOT EXISTS tag_entity_tenant_created_idx
    ON tag_entity (tenant, created_at);
CREATE INDEX IF NOT EXISTS tag_entity_slug_trgm_idx
    ON tag_entity USING GIN (slug gin_trgm_ops);
CREATE INDEX IF NOT EXISTS tag_entity_name_trgm_idx
    ON tag_entity USING GIN (name gin_trgm_ops);

-- -----------------------------------------------------------------------------
-- Core: tag_link (assignments)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_link
(
    tenant      TEXT        NOT NULL,
    entity_type TEXT        NOT NULL,
    entity_id   TEXT        NOT NULL,
    tag_id      CHAR(26)    NOT NULL,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, entity_type, entity_id, tag_id),
    CONSTRAINT tag_link_tag_fk FOREIGN KEY (tenant, tag_id)
        REFERENCES tag_entity (tenant, id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS tag_link_entity_idx
    ON tag_link (tenant, entity_type, entity_id);
CREATE INDEX IF NOT EXISTS tag_link_tag_idx
    ON tag_link (tenant, tag_id);
CREATE INDEX IF NOT EXISTS tag_link_created_idx
    ON tag_link (tenant, created_at);

-- -----------------------------------------------------------------------------
-- Synonyms
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_synonym
(
    tenant     TEXT        NOT NULL,
    id         CHAR(26)    NOT NULL,
    tag_id     CHAR(26)    NOT NULL,
    label      TEXT        NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, id)
);

DO
$$
    BEGIN
        IF NOT EXISTS (SELECT 1 as alias
                       FROM pg_constraint
                       WHERE conname = 'tag_synonym_tag_fk') THEN
            ALTER TABLE tag_synonym
                ADD CONSTRAINT tag_synonym_tag_fk
                    FOREIGN KEY (tenant, tag_id) REFERENCES tag_entity (tenant, id)
                        ON DELETE CASCADE;
        END IF;
    END
$$;

CREATE UNIQUE INDEX IF NOT EXISTS tag_synonym_uq
    ON tag_synonym (tenant, tag_id, lower(label));
CREATE INDEX IF NOT EXISTS tag_synonym_label_trgm_idx
    ON tag_synonym USING GIN (label gin_trgm_ops);

-- -----------------------------------------------------------------------------
-- Relations
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_relation
(
    tenant      TEXT        NOT NULL,
    id          CHAR(26)    NOT NULL,
    from_tag_id CHAR(26)    NOT NULL,
    to_tag_id   CHAR(26)    NOT NULL,
    type        TEXT        NOT NULL CHECK (type IN ('related', 'broader', 'narrower')),
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, id)
);

DO
$$
    BEGIN
        IF NOT EXISTS (SELECT 1 as alias2
                       FROM pg_constraint
                       WHERE conname = 'tag_relation_from_fk') THEN
            ALTER TABLE tag_relation
                ADD CONSTRAINT tag_relation_from_fk
                    FOREIGN KEY (tenant, from_tag_id) REFERENCES tag_entity (tenant, id)
                        ON DELETE CASCADE;
        END IF;
        IF NOT EXISTS (SELECT 1 as alias
                       FROM pg_constraint
                       WHERE conname = 'tag_relation_to_fk') THEN
            ALTER TABLE tag_relation
                ADD CONSTRAINT tag_relation_to_fk
                    FOREIGN KEY (tenant, to_tag_id) REFERENCES tag_entity (tenant, id)
                        ON DELETE CASCADE;
        END IF;
    END
$$;

CREATE UNIQUE INDEX IF NOT EXISTS tag_relation_uq
    ON tag_relation (tenant, from_tag_id, to_tag_id, type);

-- -----------------------------------------------------------------------------
-- Schemes
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_scheme
(
    tenant     TEXT        NOT NULL,
    id         CHAR(26)    NOT NULL,
    name       TEXT        NOT NULL,
    locale     TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, id)
);

CREATE UNIQUE INDEX IF NOT EXISTS tag_scheme_name_uq
    ON tag_scheme (tenant, lower(name));

-- -----------------------------------------------------------------------------
-- Policy (per tenant)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_policy
(
    tenant     TEXT PRIMARY KEY,
    policy     JSONB       NOT NULL DEFAULT '{}'::jsonb,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- -----------------------------------------------------------------------------
-- Proposals (moderation / review)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_proposal
(
    tenant     TEXT        NOT NULL,
    id         CHAR(26)    NOT NULL,
    type       TEXT        NOT NULL,
    payload    JSONB       NOT NULL,
    status     TEXT        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    decided_at TIMESTAMPTZ,
    decided_by TEXT,
    PRIMARY KEY (tenant, id)
);

CREATE INDEX IF NOT EXISTS tag_proposal_status_idx
    ON tag_proposal (tenant, status, created_at);

-- -----------------------------------------------------------------------------
-- Audit log
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_audit_log
(
    tenant      TEXT        NOT NULL,
    id          CHAR(26)    NOT NULL,
    action      TEXT        NOT NULL,
    entity_type TEXT        NOT NULL,
    entity_id   TEXT        NOT NULL,
    details     JSONB       NOT NULL DEFAULT '{}'::jsonb,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, id)
);

CREATE INDEX IF NOT EXISTS tag_audit_log_tenant_created_idx
    ON tag_audit_log (tenant, created_at);
CREATE INDEX IF NOT EXISTS tag_audit_log_entity_idx
    ON tag_audit_log (tenant, entity_type, entity_id, created_at);

-- -----------------------------------------------------------------------------
-- Classification (key/value metadata)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_classification
(
    tenant     TEXT        NOT NULL,
    id         CHAR(26)    NOT NULL,
    scope      TEXT        NOT NULL,
    ref_id     TEXT        NOT NULL,
    key        TEXT        NOT NULL,
    value      TEXT        NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, id)
);

CREATE UNIQUE INDEX IF NOT EXISTS tag_classification_uq
    ON tag_classification (tenant, scope, ref_id, lower(key));
CREATE INDEX IF NOT EXISTS tag_classification_lookup_idx
    ON tag_classification (tenant, scope, ref_id);

-- -----------------------------------------------------------------------------
-- Assignment effects (derived attributes)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_assignment_effect
(
    tenant        TEXT        NOT NULL,
    id            CHAR(26)    NOT NULL,
    assigned_type TEXT        NOT NULL,
    assigned_id   TEXT        NOT NULL,
    key           TEXT        NOT NULL,
    value         TEXT        NOT NULL,
    source_scope  TEXT        NOT NULL,
    source_id     TEXT        NOT NULL,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, id)
);

CREATE INDEX IF NOT EXISTS tag_assignment_effect_source_idx
    ON tag_assignment_effect (tenant, source_scope, source_id);
CREATE INDEX IF NOT EXISTS tag_assignment_effect_assigned_idx
    ON tag_assignment_effect (tenant, assigned_type, assigned_id);

-- -----------------------------------------------------------------------------
-- Redirects (slug -> tag)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tag_redirect
(
    tenant     TEXT        NOT NULL,
    from_slug  TEXT        NOT NULL,
    to_tag_id  CHAR(26)    NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (tenant, from_slug)
);

DO
$$
    BEGIN
        IF NOT EXISTS (SELECT 1 as alias
                       FROM pg_constraint
                       WHERE conname = 'tag_redirect_to_fk') THEN
            ALTER TABLE tag_redirect
                ADD CONSTRAINT tag_redirect_to_fk
                    FOREIGN KEY (tenant, to_tag_id) REFERENCES tag_entity (tenant, id)
                        ON DELETE CASCADE;
        END IF;
    END
$$;

-- -----------------------------------------------------------------------------
-- Stats view (used for facets / cloud)
-- -----------------------------------------------------------------------------

CREATE OR REPLACE VIEW tag_stats_view AS
SELECT tenant,
       entity_type      AS assigned_type,
       tag_id,
       COUNT(*)::BIGINT AS cnt
FROM tag_link
GROUP BY tenant, entity_type, tag_id;
