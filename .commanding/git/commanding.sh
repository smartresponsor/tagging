#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

COMMANDING_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
MENU_DIR="$COMMANDING_DIR/git"

repo_root() {
  git rev-parse --show-toplevel 2>/dev/null || true
}

pause_key() {
  read -rsn1 _ 2>/dev/null || true
}

print_menu() {
  printf '%s\n' ''
  printf '%s\n' ' Git'
  printf '%s\n' ' ------------------------'
  printf '%s\n' ' 1 Status'
  printf '%s\n' ' 2 Diff'
  printf '%s\n' ' 3 Sync'
  printf '%s\n' ' 4 Slice FULL (map+zip)'
  printf '%s\n' ' 5 Slice DELTA (origin/master..HEAD)'
  printf '%s\n' ' 6 Chain: Sync + Delta'
  printf '%s\n' ''
  printf '%s\n' ' 0 Back'
  printf '%s\n' ' ------------------------'
  printf '%s\n' ' Press digit (no Enter)'
}

run_file() {
  local f="$1"
  if [ ! -f "$MENU_DIR/$f" ]; then
    printf '%s\n' "Missing: $f"
    pause_key
    return 1
  fi
  bash "$MENU_DIR/$f"
}

pwsh_bin() {
  if command -v pwsh >/dev/null 2>&1; then
    printf '%s' 'pwsh'
    return 0
  fi
  if command -v powershell >/dev/null 2>&1; then
    printf '%s' 'powershell'
    return 0
  fi
  return 1
}

ensure_dir() {
  local d="$1"
  mkdir -p "$d"
}

slice_full() {
  local root
  root="$(repo_root)"
  if [ -z "${root:-}" ]; then
    printf '%s\n' "Not a git repository."
    pause_key
    return 1
  fi

  ensure_dir "$root/report/slice"

  local ps
  ps="$(pwsh_bin)" || { printf '%s\n' "pwsh/powershell not found."; pause_key; return 1; }

  "$ps" "$COMMANDING_DIR/ps1/repo-map-builder.ps1" -MakeZip -IncludeFiles \
    -OutFile "$root/report/slice/repo-map.md" \
    -ZipFile "$root/report/slice/full-slice.zip" | cat

  printf '%s\n' ''
  printf '%s\n' 'Send to ChatGPT:'
  printf '%s\n' " - report/slice/full-slice.zip"
  printf '%s\n' " - report/slice/repo-map.md"
  pause_key
}

slice_delta() {
  local root
  root="$(repo_root)"
  if [ -z "${root:-}" ]; then
    printf '%s\n' "Not a git repository."
    pause_key
    return 1
  fi

  ensure_dir "$root/report/slice"

  local ps
  ps="$(pwsh_bin)" || { printf '%s\n' "pwsh/powershell not found."; pause_key; return 1; }

  "$ps" "$COMMANDING_DIR/ps1/delta-slice-builder.ps1" \
    -BaseRef "origin/master" \
    -HeadRef "HEAD" \
    -IncludeUntracked \
    -OutDir "$root/report/slice" \
    -WriteMap | cat

  printf '%s\n' ''
  printf '%s\n' 'Send to ChatGPT:'
  printf '%s\n' " - report/slice/delta-slice.zip"
  printf '%s\n' " - report/slice/slice-meta.json"
  printf '%s\n' " - report/slice/slice-manifest.ndjson"
  printf '%s\n' " - report/slice/slice-map.md"
  pause_key
}

main_loop() {
  while true; do
    print_menu
    local ch=""
    read -rsn1 ch 2>/dev/null || return 0

    case "$ch" in
      0) return 0 ;;
      1) run_file "git_status.sh"; pause_key ;;
      2) run_file "git_diff.sh"; pause_key ;;
      3) run_file "git_sync.sh"; pause_key ;;
      4) slice_full ;;
      5) slice_delta ;;
      6) run_file "git_sync.sh" || true; slice_delta ;;
      *) ;;
    esac
  done
}

main_loop
