# Tagging internal relations Wave 3

Wave 3 intentionally does not delete entities.

The goal is to make only internal tag-domain references explicit:

- `TagRelationEntity.fromTag` -> `TagEntity`
- `TagRelationEntity.toTag` -> `TagEntity`
- `TagSynonymEntity.tag` -> `TagEntity`
- `TagRedirectEntity.toTag` -> `TagEntity`

External object tagging remains polymorphic and must not become Doctrine relations:

- `TagAssignmentEntity.assigned_type`
- `TagAssignmentEntity.assigned_id`
- `TagLinkEntity.entity_type`
- `TagLinkEntity.entity_id`

`Objecting` remains the canonical system-field source, but it does not own Tagging persistence.

## Deletion policy

Do not delete `TagLinkEntity` in this wave. It overlaps with `TagAssignmentEntity`, but removal requires usage scan across repositories, services, migrations, fixtures, tests, and UI/admin code.

Deletion can be considered only after:

1. `php bin/console doctrine:mapping:info -vvv` passes.
2. The Wave 3 audit is green.
3. A usage scan proves `TagLinkEntity` is not used as a live public contract.
4. A migration path from `tag_link` to `tag_assignment` is available.

## Doctrine note

The current Tagging model uses composite IDs with `tenant` as part of the key. Internal relations therefore use composite `JoinColumns` to keep tenant isolation.
