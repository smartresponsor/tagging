# Shell Scripts Menu

This folder contains grouped shell scripts with menu navigation.

- **Backup**: scripts related to backup
- **Database**: scripts related to database
- **Dependencies**: scripts related to dependencies
- **Deploy**: scripts related to deploy
- **Docker**: scripts related to docker
- **Git**: scripts related to git
- **Misc**: scripts related to misc
- **Tests**: scripts related to tests

Slice guide (full / delta)

Goal
- Full slice: send a complete context snapshot (zip + map + hashes).
- Delta slice: send only changes between base..head (zip + meta + manifest + mini-map).

Outputs (written under report/slice/ by default)
- slice-meta-<stamp>.json
- slice-manifest-<stamp>.ndjson
- slice-map-<stamp>.md (delta only when -WriteMap)
- full-slice.zip / delta-slice-<stamp>.zip

Exclude policy
- .commanding/policy/slice-exclude.json

Full slice (map + zip)
- pwsh .commanding/ps1/repo-map-builder.ps1 -MakeZip -OutFile report/slice/repo-map.md -ZipFile report/slice/full-slice.zip -IncludeFiles

Delta slice (git base..head)
- pwsh .commanding/ps1/delta-slice-builder.ps1 -BaseRef origin/master -HeadRef HEAD -IncludeUntracked -OutDir report/slice -WriteMap

What to send to ChatGPT
- First time (or large refactor): full-slice.zip + report/slice/repo-map.md + slice-manifest (if you generated it separately)
- Next steps: delta-slice-*.zip + slice-meta-*.json + slice-manifest-*.ndjson + slice-map-*.md
