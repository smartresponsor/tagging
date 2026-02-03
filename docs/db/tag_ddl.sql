-- baseline


-- E8: I18N labels (per-locale) and slug aliases
CREATE TABLE IF NOT EXISTS tag_label (
  id TEXT PRIMARY KEY,
  tag_id TEXT NOT NULL,
  locale TEXT NOT NULL,
  label TEXT NOT NULL,
  UNIQUE(tag_id, locale)
);
CREATE TABLE IF NOT EXISTS tag_slug_i18n (
  id TEXT PRIMARY KEY,
  tag_id TEXT NOT NULL,
  locale TEXT NOT NULL,
  slug TEXT NOT NULL,
  UNIQUE(tag_id, locale),
  UNIQUE(slug, locale)
);


-- E9: Quotas & Rate limits
CREATE TABLE IF NOT EXISTS tag_write_log (
  id TEXT PRIMARY KEY,
  actor_id TEXT NOT NULL,
  op TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS tag_write_log_actor_idx ON tag_write_log(actor_id, created_at);

CREATE TABLE IF NOT EXISTS tag_quota_config (
  id SMALLINT PRIMARY KEY DEFAULT 1,
  per_minute INTEGER NOT NULL DEFAULT 60,
  max_tags_per_entity INTEGER NOT NULL DEFAULT 250
);
INSERT INTO tag_quota_config(id) VALUES (1) ON CONFLICT (id) DO NOTHING;


-- E12: Synonyms & Relations
CREATE TABLE IF NOT EXISTS tag_synonym (
  id TEXT PRIMARY KEY,
  tag_id TEXT NOT NULL,
  label TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  UNIQUE(tag_id, label)
);

CREATE TABLE IF NOT EXISTS tag_relation (
  id TEXT PRIMARY KEY,
  from_tag_id TEXT NOT NULL,
  to_tag_id TEXT NOT NULL,
  type TEXT NOT NULL CHECK (type IN ('related','broader','narrower')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  UNIQUE(from_tag_id, to_tag_id, type)
);

-- Optional helper view: symmetric "related" (ignores direction for 'related')
CREATE VIEW IF NOT EXISTS tag_relation_related AS
SELECT from_tag_id AS a, to_tag_id AS b FROM tag_relation WHERE type='related'
UNION
SELECT to_tag_id AS a, from_tag_id AS b FROM tag_relation WHERE type='related';


-- E13: Bulk import/export + Merge/Split + Redirects
CREATE TABLE IF NOT EXISTS tag_redirect (
  id TEXT PRIMARY KEY,
  from_tag_id TEXT NOT NULL UNIQUE,
  to_tag_id TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS tag_merge_log (
  id TEXT PRIMARY KEY,
  from_tag_id TEXT NOT NULL,
  to_tag_id TEXT NOT NULL,
  actor_id TEXT,
  counts_before JSONB DEFAULT '{}'::jsonb,
  counts_after JSONB DEFAULT '{}'::jsonb,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS tag_bulk_job (
  id TEXT PRIMARY KEY,
  type TEXT NOT NULL CHECK (type IN ('import','export')),
  status TEXT NOT NULL CHECK (status IN ('queued','running','done','failed')) DEFAULT 'queued',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  started_at TIMESTAMPTZ,
  finished_at TIMESTAMPTZ,
  error TEXT
);

CREATE TABLE IF NOT EXISTS tag_bulk_item (
  id TEXT PRIMARY KEY,
  job_id TEXT NOT NULL,
  payload JSONB NOT NULL,
  status TEXT NOT NULL CHECK (status IN ('queued','ok','error')) DEFAULT 'queued',
  message TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS tag_bulk_item_job_idx ON tag_bulk_item(job_id);


-- E14 hardening added; see e14_hardening_postgres.sql and e14_hardening_mysql.sql
