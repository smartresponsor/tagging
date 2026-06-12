# Tagging Objecting System Field Canon

Tagging consumes Objecting as a system field-pack foundation, not as a persistence owner.

## Ownership boundary

- Tagging owns Doctrine entities, repositories, forms, DTOs, services, controllers/http services, business rules, and migrations.
- Objecting owns reusable system field packs only: `object_identity`, `object_audit`, `object_title`, and optional lifecycle packs.
- Objecting profiles are declarations for migration/audit/navigation. They are not base entities and not a replacement for Tagging runtime ownership.

## Required baseline for `TagEntity`

`TagEntity` should declare the local Objecting consumer contract:

- `field_pack_profile: object_baseline`
- `field_packs: object_identity`, `object_audit`, `object_title`
- `title_alias_profile: object_title_label`
- title aliases: `firstTitle -> label`, `middleTitle -> summary`, `lastTitle -> description`

The backend primary key `id` remains Tagging-owned. Do not blindly replace it with `object_uuid`.

## Field classification

### Objecting-owned system fields

These should be migrated to Objecting field packs when the Doctrine mapping is clean:

- object identity: `object_uuid`, `object_slug`
- object audit: `object_created_at`, `object_updated_at`, `object_created_by`, `object_updated_by`
- object title: `object_first_title`, `object_middle_title`, `object_last_title`

### Tagging-owned domain fields

These stay in Tagging:

- `tenant`
- backend primary `id`
- `tag_id`, `from_tag_id`, `to_tag_id`
- `assigned_type`, `assigned_id`
- `relation_type` / `type`
- `required_flag`, `mod_only_flag`
- policy/proposal/audit/outbox payload fields

### Business title aliases

`label`, `summary`, and `description` are Tagging-facing aliases. They may be mapped to Objecting title fields, but API/DTO/Form names may remain local.

## Relation canon

### External taggable objects

Do not create Doctrine relations from `TagAssignmentEntity` to every possible target component.

Use polymorphic reference fields:

- `assigned_type`
- `assigned_id`

This preserves component isolation.

### Internal tag graph

Internal tag-to-tag relations are inside Tagging and may use Doctrine relations or explicit FK checks:

- `TagRelationEntity.fromTag -> TagEntity`
- `TagRelationEntity.toTag -> TagEntity`
- `TagSynonymEntity.tag -> TagEntity`
- `TagRedirectEntity.toTag -> TagEntity`

## Migration order

1. Fix invalid Doctrine named arguments: `nameEntity` must become `name` in mapping attributes.
2. Add Objecting consumer declaration under `Tagging/resources/objecting/Tag/object-field-packs.yaml`.
3. Run `Tagging/tools/inspection/tagging-objecting-system-field-audit.php`.
4. Migrate only canonical mutable object entities first, starting with `TagEntity`.
5. Keep operational entities (`outbox`, `idempotency`, `audit log`, projections) lightweight and do not over-Objecting them.
6. Add internal tag graph relations after field mapping is stable.

## Do not do

- Do not introduce `/src/Domain`.
- Do not introduce Port/Adapter naming.
- Do not make Tagging depend on every taggable backend component.
- Do not remove local primary keys because Objecting has `object_uuid`.
- Do not migrate projections/outbox/idempotency before the core tag model is stable.
