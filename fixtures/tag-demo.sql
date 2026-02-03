-- Demo fixtures for tenant 'demo' (RC5-E8)
-- Inserts use ON CONFLICT to be idempotent.
-- ULIDs are produced by the seeder script; this file is informational or for manual ad-hoc seeding.

-- Example:
-- INSERT INTO tag_entity (id, tenant, slug, name, locale, weight)
-- VALUES ('01HSEEDSUMMER00000000000000', 'demo', 'summer-sale', 'Summer Sale', 'en', 20)
-- ON CONFLICT (tenant, slug) DO NOTHING;
