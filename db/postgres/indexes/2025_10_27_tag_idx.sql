-- Indexes for Tag component — RC5-E2
-- Generated: 2025-10-27T20:27:58.491956

-- Fast lookup by slug/name with trigram search and prefix
CREATE INDEX IF NOT EXISTS tag_entity_slug_trgm_idx
    ON tag_entity USING GIN (slug gin_trgm_ops);
CREATE INDEX IF NOT EXISTS tag_entity_name_trgm_idx
    ON tag_entity USING GIN (name gin_trgm_ops);

-- Support for equality filters
CREATE INDEX IF NOT EXISTS tag_entity_tenant_name_idx
    ON tag_entity (tenant, name);
CREATE INDEX IF NOT EXISTS tag_entity_tenant_weight_idx
    ON tag_entity (tenant, weight);

-- Link lookups per entity
CREATE INDEX IF NOT EXISTS tag_link_entity_idx
    ON tag_link (tenant, entity_type, entity_id);

-- Reverse lookups (all entities for tag)
CREATE INDEX IF NOT EXISTS tag_link_tag_idx
    ON tag_link (tenant, tag_id);
