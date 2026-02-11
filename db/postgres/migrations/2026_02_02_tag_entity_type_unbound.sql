-- Tag component schema patch — unbound entity_type allowlist
-- Generated: 2026-02-02
-- Goal: remove hard-coded DB CHECK constraint for tag_link.entity_type.
-- Validation/allowlist is enforced at application layer (config/tag_assignment.yaml).

DO
$$
    DECLARE
        c_name text;
    BEGIN
        IF to_regclass('public.tag_link') IS NULL THEN
            RETURN;
        END IF;

        -- Default inline CHECK name (created in 2025_10_27_tag.sql)
        ALTER TABLE tag_link
            DROP CONSTRAINT IF EXISTS tag_link_entity_type_check;

        -- Safety: drop any remaining CHECK that references entity_type.
        SELECT conname
        INTO c_name
        FROM pg_constraint
        WHERE conrelid = 'tag_link'::regclass
          AND contype = 'c'
          AND pg_get_constraintdef(oid) LIKE '%entity_type%'
        LIMIT 1;

        IF c_name IS NOT NULL THEN
            EXECUTE format('ALTER TABLE tag_link DROP CONSTRAINT %I', c_name);
        END IF;
    END
$$;
