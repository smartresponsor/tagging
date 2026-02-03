-- Enable fuzzy/partial search on tag.label/slug and synonyms.
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- Assuming table tag(id uuid, label text, slug text, usage_count int, ...)
CREATE INDEX IF NOT EXISTS idx_tag_label_trgm ON tag USING gin (label gin_trgm_ops);
CREATE INDEX IF NOT EXISTS idx_tag_slug_trgm  ON tag USING gin (slug  gin_trgm_ops);

-- Synonyms table from E28
-- CREATE TABLE tag_synonym(id uuid, tag_id uuid, label text, created_at timestamptz, ...)
CREATE INDEX IF NOT EXISTS idx_tag_synonym_trgm ON tag_synonym USING gin (label gin_trgm_ops);

-- Assignments from E27
-- CREATE TABLE tag_assignment(id uuid, tag_id uuid, entity_type text, entity_id text, created_at timestamptz, ...)
CREATE INDEX IF NOT EXISTS idx_tag_assignment_tag_entity ON tag_assignment(tag_id, entity_type, entity_id);
