-- Tag assignment matrix (PostgreSQL)
-- Tables: tag (assumed existing), tag_assignment
-- Singluar naming, ULID/UUID ids.

CREATE TABLE IF NOT EXISTS tag_assignment
(
    id          uuid PRIMARY KEY     DEFAULT gen_random_uuid(),
    tag_id      uuid        NOT NULL,
    entity_type text        NOT NULL CHECK (entity_type ~ '^[a-z][a-z0-9_]*$'),
    entity_id   text        NOT NULL, -- allow ULID/UUID/slug; validated at application layer
    created_at  timestamptz NOT NULL DEFAULT now(),
    UNIQUE (entity_type, entity_id, tag_id),
    FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE
);

-- Indexes for lookup
CREATE INDEX IF NOT EXISTS idx_tag_assignment_tag ON tag_assignment (tag_id);
CREATE INDEX IF NOT EXISTS idx_tag_assignment_entity ON tag_assignment (entity_type, entity_id);
