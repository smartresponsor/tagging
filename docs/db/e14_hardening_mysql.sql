-- E14 (MySQL 8) — Data hardening & indexes
CREATE TABLE IF NOT EXISTS tag (
  id VARCHAR(36) PRIMARY KEY,
  slug VARCHAR(255) NOT NULL,
  label VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE UNIQUE INDEX tag_slug_unique ON tag((LOWER(slug)));

CREATE TABLE IF NOT EXISTS tag_assignment (
  id VARCHAR(36) PRIMARY KEY,
  tag_id VARCHAR(36) NOT NULL,
  assigned_type VARCHAR(64) NOT NULL,
  assigned_id VARCHAR(128) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tag_assignment_tag FOREIGN KEY (tag_id) REFERENCES tag(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE UNIQUE INDEX tag_assignment_unique ON tag_assignment(tag_id, assigned_type, assigned_id);

-- i18n
CREATE UNIQUE INDEX tag_label_unique ON tag_label(tag_id, locale);
CREATE UNIQUE INDEX tag_slug_i18n_pair_unique ON tag_slug_i18n(tag_id, locale);
CREATE UNIQUE INDEX tag_slug_i18n_slug_unique ON tag_slug_i18n(locale, slug);

-- synonyms & relations
CREATE UNIQUE INDEX tag_synonym_unique ON tag_synonym(tag_id, label);
CREATE UNIQUE INDEX tag_relation_unique ON tag_relation(from_tag_id, to_tag_id, type);
ALTER TABLE tag_relation
  ADD CONSTRAINT fk_tag_relation_from FOREIGN KEY (from_tag_id) REFERENCES tag(id) ON DELETE RESTRICT,
  ADD CONSTRAINT fk_tag_relation_to FOREIGN KEY (to_tag_id) REFERENCES tag(id) ON DELETE RESTRICT;

-- write log & quotas
CREATE INDEX tag_write_log_actor_idx ON tag_write_log(actor_id, created_at);
-- redirect & merges
CREATE UNIQUE INDEX tag_redirect_from_unique ON tag_redirect(from_tag_id);
ALTER TABLE tag_redirect
  ADD CONSTRAINT fk_tag_redirect_from FOREIGN KEY (from_tag_id) REFERENCES tag(id) ON DELETE RESTRICT,
  ADD CONSTRAINT fk_tag_redirect_to FOREIGN KEY (to_tag_id) REFERENCES tag(id) ON DELETE RESTRICT;
