param(
    [Parameter(Mandatory = $false)]
    [string]$RepoRoot
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($RepoRoot)) {
    if (-not [string]::IsNullOrWhiteSpace($PSScriptRoot)) {
        $RepoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
    } else {
        $RepoRoot = (Get-Location).Path
    }
}

$runner = Join-Path $RepoRoot 'tools\test\tag-ci-bridge-wave27.php'
if (-not (Test-Path -LiteralPath $runner)) {
    throw "Missing Tagging CI bridge runner: $runner"
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
