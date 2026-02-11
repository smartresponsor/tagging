-- RC5-E6: Slug policy hardening — ensure lower-case and length bound
-- Generated: 2025-10-27T20:54:00.810192

ALTER TABLE tag_entity
    ALTER COLUMN slug TYPE TEXT;

DO
$$
    BEGIN
        IF NOT EXISTS (SELECT 1
                       FROM pg_constraint
                       WHERE conname = 'tag_entity_slug_lower_ck') THEN
            ALTER TABLE tag_entity
                ADD CONSTRAINT tag_entity_slug_lower_ck CHECK (slug = lower(slug));
        END IF;
    END;
$$;

DO
$$
    BEGIN
        IF NOT EXISTS (SELECT 1
                       FROM pg_constraint
                       WHERE conname = 'tag_entity_slug_len_ck') THEN
            ALTER TABLE tag_entity
                ADD CONSTRAINT tag_entity_slug_len_ck CHECK (length(slug) BETWEEN 2 AND 64);
        END IF;
    END;
$$;
