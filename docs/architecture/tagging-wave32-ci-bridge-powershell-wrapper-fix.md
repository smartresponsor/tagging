# Tagging Wave 32 — CI Bridge PowerShell Wrapper Fix

## Scope

Wave 32 fixes the PowerShell convenience wrapper added in Wave 28.

This wave does **not** rename files, move classes, delete files, or change namespaces.
`App\Tagging\...` remains canonical.

## Problem

When launched in some PowerShell contexts, `$PSScriptRoot` can be empty while evaluating the default parameter value.

## Fix

`RepoRoot` is now resolved inside the script body:

1. use explicit `-RepoRoot` when supplied;
2. otherwise use `$PSScriptRoot` when available;
3. otherwise fall back to the current working directory.

## Verification

```bash
vendor/bin/phpunit tests/TagCiBridgeCommandWrapperWave32Test.php
```

PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File tools/test/tag-ci-bridge-wave28.ps1
```
