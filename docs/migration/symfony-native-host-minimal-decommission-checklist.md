# Symfony-native host-minimal decommission checklist

## Purpose

This checklist defines the final review gate for reducing `host-minimal` from a compatibility bridge to a historical or removed runtime path.

It is intended to be used after:

- Symfony-native runtime baseline is active
- Wave 2 bridge has been applied
- residual references have been reviewed

## Execution checklist

### Runtime truth

- [ ] `public/index.php` is Symfony-native and no longer delegates to `host-minimal`
- [ ] `tag.yaml` describes Symfony-native runtime truth
- [ ] runtime docs no longer present `host-minimal` as the current strategic runtime model

### Controller and DI truth

- [ ] controllers are resolved through Symfony DI
- [ ] write use cases are resolved through Symfony DI
- [ ] repository and transaction seams are resolved through Symfony DI aliases/configuration
- [ ] `host-minimal/bootstrap.php` no longer acts as the source of truth for controller graph assembly

### Compatibility path decision

- [ ] confirm whether `host-minimal/index.php` is still needed for any active caller
- [ ] confirm whether `host-minimal/route.php` is still needed for any active caller
- [ ] if still needed, document them as temporary compatibility-only artifacts
- [ ] if not needed, remove them in the cleanup wave

### Scripts and tooling

- [ ] local serve scripts no longer assume `host-minimal` as the primary entry
- [ ] audits do not misreport `host-minimal` as the active runtime owner
- [ ] migration apply scripts and docs remain coherent with the current repository state

### Documentation and historical notes

- [ ] old ADRs are clearly historical when they refer to the old runtime model
- [ ] installation and ops docs reflect Symfony-native runtime first
- [ ] stale migration notes are either removed or marked historical-only

## Completion signal

This checklist is complete when repository readers, operators, and tooling can no longer confuse `host-minimal` with the active primary runtime path, and any remaining `host-minimal` files are clearly transitional or historical.
