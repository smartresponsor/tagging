param(
    [Parameter(Mandatory = $false)]
    [string]$RepoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
)

$ErrorActionPreference = 'Stop'

$runner = Join-Path $RepoRoot 'tools\test\tag-post-canon-all-wave22.php'
if (-not (Test-Path -LiteralPath $runner)) {
    throw "Missing complete post-canon runner: $runner"
}

$composerPath = Join-Path $RepoRoot 'composer.json'
if (-not (Test-Path -LiteralPath $composerPath)) {
    throw "Missing composer.json: $composerPath"
}

$composerRaw = Get-Content -LiteralPath $composerPath -Raw
if (-not $composerRaw.Contains('App\\Tagging\\')) {
    throw 'composer.json must keep App\Tagging\ as the component namespace.'
}

Push-Location $RepoRoot
try {
    & php $runner
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}
finally {
    Pop-Location
}
