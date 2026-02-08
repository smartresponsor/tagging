Param(
  [switch]$NoStart
)

$ErrorActionPreference = "Stop"

$PostgresDb = $env:POSTGRES_DB; if (-not $PostgresDb) { $PostgresDb = "app" }
$PostgresUser = $env:POSTGRES_USER; if (-not $PostgresUser) { $PostgresUser = "app" }
$PostgresPassword = $env:POSTGRES_PASSWORD; if (-not $PostgresPassword) { $PostgresPassword = "app" }
$DbHost = $env:DB_HOST; if (-not $DbHost) { $DbHost = "127.0.0.1" }
$DbPort = $env:DB_PORT; if (-not $DbPort) { $DbPort = "5432" }

function Invoke-Psql([string]$Sql) {
  $env:PGPASSWORD = $PostgresPassword
  & psql -h $DbHost -p $DbPort -U $PostgresUser -d $PostgresDb -v ON_ERROR_STOP=1 -c $Sql | Out-Null
}

if (-not $NoStart) {
  Write-Host "[migration-smoke] starting docker compose db"
  docker compose up -d db | Out-Null
}

Write-Host "[migration-smoke] waiting for Postgres at $DbHost`:$DbPort"
$ok = $false
for ($i=0; $i -lt 40; $i++) {
  try { Invoke-Psql "select 1"; $ok = $true; break } catch { Start-Sleep -Seconds 1 }
}
if (-not $ok) { throw "Postgres not ready" }

Write-Host "[migration-smoke] applying migrations from db/postgres/migrations"
$migs = Get-ChildItem -Path "db/postgres/migrations" -Filter "*.sql" | Sort-Object Name
if ($migs.Count -eq 0) { throw "No migrations found" }

foreach ($m in $migs) {
  Write-Host "[migration-smoke] apply $($m.Name)"
  $env:PGPASSWORD = $PostgresPassword
  & psql -h $DbHost -p $DbPort -U $PostgresUser -d $PostgresDb -v ON_ERROR_STOP=1 -f $m.FullName | Out-Null
}

Write-Host "[migration-smoke] verifying critical tables"
$required = @("tag_entity","tag_relation","tag_policy","tag_audit_log","outbox_event","idempotency_store")
foreach ($t in $required) {
  $env:PGPASSWORD = $PostgresPassword
  $out = & psql -h $DbHost -p $DbPort -U $PostgresUser -d $PostgresDb -t -A -c "select count(*) from information_schema.tables where table_name='$t'" 2>$null
  if ($out.Trim() -ne "1") { throw "Missing table: $t" }
}

Write-Host "[migration-smoke] OK"

if (-not $NoStart) {
  Write-Host "[migration-smoke] stopping docker compose"
  docker compose down -v | Out-Null
}
