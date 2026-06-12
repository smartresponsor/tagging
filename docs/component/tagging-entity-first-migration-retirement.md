# Tagging entity-first migration retirement

## Scope

Current slice: `Tagging` from `www-clean-20260610-170157(1).zip` only.
Legacy donor checked: `Entity-src(6).zip`.

## Retired schema-first sources

- `Tagging/db/postgres/migrations/**`
- `Tagging/migrations/**`
- `Tagging/tools/migration/**`

These files are no longer allowed to define Tagging persistence shape at this stage.
Doctrine entities are now the source of truth.

## Entity-first coverage

Existing runtime entities already covered all migration tables:

- `tag_entity` → `App\Tagging\Data\Model\Tag\TagEntity`
- `tag` → `App\Tagging\Entity\Core\Tag\Tag`
- `tag_admin_view` → `TagAdminView`
- `tag_assignment` → `TagAssignment`
- `tag_assignment_effect` → `TagAssignmentEffect`
- `tag_audit_log` → `TagAuditLog`
- `tag_classification` → `TagClassification`
- `idempotency_store` → `TagIdempotencyStore`
- `tag_link` → `TagLink`
- `outbox_event` → `TagOutboxEvent`
- `tag_policy` → `TagPolicy`
- `tag_proposal` → `TagProposal`
- `tag_redirect` → `TagRedirect`
- `tag_relation` → `TagRelation`
- `tag_scheme` → `TagScheme`
- `tag_synonym` → `TagSynonym`

## Migration-owned metadata moved into Doctrine attributes

- `tag_entity.required_flag`
- `tag_entity.mod_only_flag`
- `tag_entity_tenant_created_idx`
- `tag_admin_view_tag_uq`
- `tag_admin_view_slug_uq`
- `tag_link_entity_idx`
- `tag_link_tag_idx`
- `tag_link_created_idx`
- `outbox_event_tenant_created_idx`
- `tag_proposal_status_idx`
- `tag_audit_log_tenant_created_idx`
- `tag_audit_log_entity_idx`
- `tag_classification_lookup_idx`
- `tag_assignment_effect_source_idx`
- `tag_assignment_effect_assigned_idx`

PostgreSQL-specific trigram/GIN indexes and trigger functions are intentionally not represented as entity fields.
They should become explicit platform infrastructure later, not migration-owned entity schema.

## Legacy monolith reconciliation

Legacy files checked:

- `Entity/Product/ProductTag.php`
- `Entity/Project/ProjectTag.php`
- `EntityInterface/Tag/TagInterface.php`
- `EntityInterface/Product/ProductTagInterface.php`

Old direct ManyToMany dependencies were not copied into Tagging because that would re-couple Tagging to Product/Project components.
They are normalized through `TagLink` boundary references:

- `TagLink::forProduct()`
- `TagLink::forProject()`
- `TagLink::forCategory()`
- `TagLink::forText()`

Compatibility interfaces were added under `App\Tagging\EntityInterface\Tag`.

## Objecting decision

Tagging already has explicit tenant/composite-key lifecycle fields required by its runtime contracts.
No new local generic Objecting duplicate fields were introduced.
Existing `createdAt`, `updatedAt`, `status`, `deliveredAt`, `decidedAt`, and idempotency state fields remain local because they are operation/audit/idempotency lifecycle facts, not generic Objecting profile duplication.

## Repository contracts

Repository interfaces and concrete repositories were added for all `Entity/Core/Tag` models and `Data/Model/Tag/TagEntity`.
Entity metadata now points to repository classes.
