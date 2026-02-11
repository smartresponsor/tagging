-- Tenancy for tag and tag_assignment
ALTER TABLE IF EXISTS tag
    ADD COLUMN IF NOT EXISTS tenant_id text;
ALTER TABLE IF EXISTS tag_assignment
    ADD COLUMN IF NOT EXISTS tenant_id text;

-- Composite uniqueness with tenant
-- (assuming 'tag' has UNIQUE(slug); we scope it to tenant if needed)
-- You may replace existing unique constraints accordingly.

-- Indexes
CREATE INDEX IF NOT EXISTS idx_tag_tenant ON tag (tenant_id);
CREATE INDEX IF NOT EXISTS idx_tag_assignment_tenant ON tag_assignment (tenant_id);
CREATE INDEX IF NOT EXISTS idx_tag_assignment_tenant_entity ON tag_assignment (tenant_id, entity_type, entity_id);
