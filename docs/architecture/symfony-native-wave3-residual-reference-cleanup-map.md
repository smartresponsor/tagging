# Symfony-native Wave 3 residual reference cleanup map

## Purpose

This map identifies the remaining repository areas that are likely to retain `host-minimal` references even after the Wave 2 compatibility bridge is applied.

The goal is to prepare a near-final cleanup bundle that removes stale operational and documentation truth rather than only changing executable paths.

## Residual reference zones

### Documentation and ADR history

Potential residual areas include:

- deployment notes
- runtime wiring documentation
- ops/runbook material
- historical ADRs that still describe `host-minimal` as current truth
- release/demo notes written before Symfony-native alignment

### Tooling and local scripts

Potential residual areas include:

- local serve scripts
- host helper scripts
- audit scripts that still assume bootstrap/runtime ownership by `host-minimal`
- phpstan/bootstrap tooling that may still mention old runtime entry assumptions

### Generated and derived configuration artifacts

Potential residual areas include:

- runtime projections
- public surface projections
- route catalogs
- release/readiness artifacts that embed old runtime wording

## Cleanup principle

Residual references should be classified into three groups:

### 1. Keep as historical context only

Examples:

- ADRs that remain useful as history but must not compete with current runtime truth

### 2. Rewrite to Symfony-native current truth

Examples:

- docs that are still meant to guide active installation, runtime, or operations
- scripts and helpers that are still part of the current developer workflow

### 3. Remove entirely

Examples:

- no-longer-used compatibility notes
- stale helper scripts tied only to the old runtime path
- redundant migration-era explanations after cutover is complete

## Recommended Wave 3 cleanup order

### Step 1

Rewrite active docs and scripts that still misstate the primary runtime path.

### Step 2

Downgrade old ADR/runtime notes to historical context where keeping them is still useful.

### Step 3

Remove obsolete helpers and notes that no longer serve either runtime or historical value.

## Success signal

Wave 3 residual cleanup is materially complete when repository readers and operators can no longer mistake `host-minimal` for the active strategic runtime model.
