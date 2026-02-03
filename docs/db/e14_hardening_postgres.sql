-- E14 (PostgreSQL) — Data hardening & indexes
-- Core tables (idempotent guards)
CREATE TABLE IF NOT EXISTS tag (
  id TEXT PRIMARY KEY,
  slug TEXT NOT NULL,
  label TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE UNIQUE INDEX IF NOT EXISTS tag_slug_unique ON tag(LOWER(slug));
CREATE INDEX IF NOT EXISTS tag_created_idx ON tag(created_at);

CREATE TABLE IF NOT EXISTS tag_assignment (
  id TEXT PRIMARY KEY,
  tag_id TEXT NOT NULL,
  assigned_type TEXT NOT NULL,
  assigned_id TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
-- Uniqueness per assignment triple
CREATE UNIQUE INDEX IF NOT EXISTS tag_assignment_unique ON tag_assignment(tag_id, assigned_type, assigned_id);
-- Foreign keys (add once; names chosen for clarity)
DO $$ BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint WHERE conname = 'fk_tag_assignment_tag'
  ) THEN
    ALTER TABLE tag_assignment
      ADD CONSTRAINT fk_tag_assignment_tag FOREIGN KEY (tag_id) REFERENCES tag(id) ON DELETE RESTRICT;
  END IF;
END $$;

-- i18n labels
CREATE UNIQUE INDEX IF NOT EXISTS tag_label_unique ON tag_label(tag_id, locale);
-- i18n slugs
CREATE UNIQUE INDEX IF NOT EXISTS tag_slug_i18n_pair_unique ON tag_slug_i18n(tag_id, locale);
CREATE UNIQUE INDEX IF NOT EXISTS tag_slug_i18n_slug_unique ON tag_slug_i18n(locale, slug);

-- synonyms & relations
CREATE UNIQUE INDEX IF NOT EXISTS tag_synonym_unique ON tag_synonym(tag_id, label);
CREATE UNIQUE INDEX IF NOT EXISTS tag_relation_unique ON tag_relation(from_tag_id, to_tag_id, type);
DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname='fk_tag_relation_from') THEN
    ALTER TABLE tag_relation ADD CONSTRAINT fk_tag_relation_from FOREIGN KEY (from_tag_id) REFERENCES tag(id) ON DELETE RESTRICT;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname='fk_tag_relation_to') THEN
    ALTER TABLE tag_relation ADD CONSTRAINT fk_tag_relation_to FOREIGN KEY (to_tag_id) REFERENCES tag(id) ON DELETE RESTRICT;
  END IF;
END $$;

-- write log & quotas
CREATE INDEX IF NOT EXISTS tag_write_log_actor_idx ON tag_write_log(actor_id, created_at);
-- redirect & merges
CREATE UNIQUE INDEX IF NOT EXISTS tag_redirect_from_unique ON tag_redirect(from_tag_id);
DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname='fk_tag_redirect_from') THEN
    ALTER TABLE tag_redirect ADD CONSTRAINT fk_tag_redirect_from FOREIGN KEY (from_tag_id) REFERENCES tag(id) ON DELETE RESTRICT;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname='fk_tag_redirect_to') THEN
    ALTER TABLE tag_redirect ADD CONSTRAINT fk_tag_redirect_to FOREIGN KEY (to_tag_id) REFERENCES tag(id) ON DELETE RESTRICT;
  END IF;
END $$;
