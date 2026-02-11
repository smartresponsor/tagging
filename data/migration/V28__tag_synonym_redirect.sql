-- Tag synonyms and redirects (PostgreSQL)
-- tag_synonym: (tag_id,label) unique per tag, label lowercase/normalized
-- tag_redirect: (from_id -> to_id) with uniqueness on from_id

CREATE TABLE IF NOT EXISTS tag_synonym
(
    id         uuid PRIMARY KEY     DEFAULT gen_random_uuid(),
    tag_id     uuid        NOT NULL,
    label      text        NOT NULL,
    created_at timestamptz NOT NULL DEFAULT now(),
    UNIQUE (tag_id, label),
    FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_tag_synonym_label ON tag_synonym (lower(label));

CREATE TABLE IF NOT EXISTS tag_redirect
(
    from_id    uuid PRIMARY KEY,
    to_id      uuid        NOT NULL,
    created_at timestamptz NOT NULL DEFAULT now(),
    FOREIGN KEY (to_id) REFERENCES tag (id) ON DELETE CASCADE
);
