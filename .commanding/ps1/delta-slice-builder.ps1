param(
    [string]$Root = "",
    [string]$BaseRef = "origin/master",
    [string]$HeadRef = "HEAD",
    [switch]$IncludeUntracked,
    [string]$OutDir = "report/slice",
    [switch]$WriteMap
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function RepoRoot([string]$RootArg)
{
    if ($RootArg -and (Test-Path $RootArg))
    {
        return (Resolve-Path $RootArg).Path
    }
    try
    {
        $r = git rev-parse --show-toplevel 2> $null
        if ($LASTEXITCODE -eq 0 -and $r)
        {
            return $r.Trim()
        }
    }
    catch
    {
    }
    throw "Not a git repository (or git not available)."
}

function LoadExcludeDir([string]$CommandingDir)
{
    $p = Join-Path $CommandingDir "policy/slice-exclude.json"
    if (-not (Test-Path $p))
    {
        return @()
    }
    try
    {
        $policy = Get-Content -Raw -Path $p | ConvertFrom-Json
        if ($policy.excludeDir)
        {
            return @($policy.excludeDir)
        }
    }
    catch
    {
    }
    return @()
}

function IsExcluded([string]$RelPath, [string[]]$ExcludeDir)
{
    $norm = $RelPath -replace "\\", "/"
    foreach ($d in $ExcludeDir)
    {
        $dn = $d -replace "\\", "/"
        if ($norm -eq $dn)
        {
            return $true
        }
        if ( $norm.StartsWith($dn.TrimEnd("/") + "/"))
        {
            return $true
        }
    }
    return $false
}

function Sha256File([string]$Path)
{
    $h = Get-FileHash -Algorithm SHA256 -Path $Path
    return $h.Hash.ToLowerInvariant()
}

function EnsureDir([string]$p)
{
    if (-not (Test-Path $p))
    {
        New-Item -ItemType Directory -Path $p | Out-Null
    }
}

function WriteNdjson([string]$Path, [object[]]$Items)
{
    $sb = New-Object System.Text.StringBuilder
    foreach ($it in $Items)
    {
        $line = ($it | ConvertTo-Json -Compress)
        [void]$sb.AppendLine($line)
    }
    [IO.File]::WriteAllText($Path,$sb.ToString(), (New-Object System.Text.UTF8Encoding($false)))
}

function ZipFiles([string]$ZipPath, [string]$RootAbs, [object[]]$Files)
{
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    if (Test-Path $ZipPath)
    {
        Remove-Item -Force $ZipPath
    }
    $zip = [System.IO.Compression.ZipFile]::Open($ZipPath, [System.IO.Compression.ZipArchiveMode]::Create)

    try
    {
        foreach ($f in $Files)
        {
            if ($f.status -eq "D")
            {
                continue
            } # deletes are only in manifest
            $rel = $f.path -replace "\\", "/"
            $abs = Join-Path $RootAbs $rel
            if (-not (Test-Path $abs))
            {
                continue
            }
            # ensure directory entries not needed; zip stores full path within entry
            [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $abs, $rel, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
        }
    }
    finally
    {
        $zip.Dispose()
    }
}

function MiniMap([string]$MapPath, [object[]]$Files)
{
    $paths = @($Files | Where-Object { $_.status -ne "D" } | ForEach-Object { $_.path }) | Sort-Object -Unique
    $lines = @()
    $lines += "# slice-map (delta)"
    $lines += ""
    foreach ($p in $paths)
    {
        $lines += $p
    }
    [IO.File]::WriteAllLines($MapPath, $lines, (New-Object System.Text.UTF8Encoding($false)))
}

$rootAbs = RepoRoot $Root
$commandingDir = Join-Path $rootAbs ".commanding"
$excludeDir = LoadExcludeDir $commandingDir

$outAbs = Join-Path $rootAbs $OutDir
EnsureDir $outAbs

# Collect changes from git
$diff = git diff --name-status "$BaseRef..$HeadRef"
if ($LASTEXITCODE -ne 0)
{
    throw "git diff failed for range $BaseRef..$HeadRef"
}

$items = @()
foreach ($line in $diff)
{
    if (-not $line)
    {
        continue
    }
    $parts = $line -split "`t"
    $statusRaw = $parts[0].Trim()
    $path = $parts[-1].Trim()
    if (-not $path)
    {
        continue
    }

    # normalize rename status (Rxxx)
    $status = $statusRaw
    if ( $statusRaw.StartsWith("R"))
    {
        $status = "R"
    }

    if (IsExcluded $path $excludeDir)
    {
        continue
    }

    $abs = Join-Path $rootAbs $path
    $size = 0
    $sha = ""
    if ($status -ne "D" -and (Test-Path $abs))
    {
        $fi = Get-Item -LiteralPath $abs
        $size = [int64]$fi.Length
        $sha = Sha256File $abs
    }

    $items += [pscustomobject]@{
        path = ($path -replace "\\", "/")
        status = $status
        sha256 = $sha
        size = $size
    }
}

if ($IncludeUntracked)
{
    $untracked = git ls-files --others --exclude-standard
    foreach ($p in $untracked)
    {
        if (-not $p)
        {
            continue
        }
        if (IsExcluded $p $excludeDir)
        {
            continue
        }
        $abs = Join-Path $rootAbs $p
        if (-not (Test-Path $abs))
        {
            continue
        }
        $fi = Get-Item -LiteralPath $abs
        $items += [pscustomobject]@{
            path = ($p -replace "\\", "/")
            status = "A"
            sha256 = (Sha256File $abs)
            size = [int64]$fi.Length
        }
    }
}

# Deduplicate by path, keep last
$byPath = @{ }
foreach ($it in $items)
{
    $byPath[$it.path] = $it
}
$items = @($byPath.Values | Sort-Object path)

$meta = [pscustomobject]@{
    mode = "delta"
    baseRef = $BaseRef
    headRef = $HeadRef
    includeUntracked = [bool]$IncludeUntracked
    generatedAt = (Get-Date).ToUniversalTime().ToString("o")
}

$metaPath = Join-Path $outAbs "slice-meta.json"
$manifestPath = Join-Path $outAbs "slice-manifest.ndjson"
$mapPath = Join-Path $outAbs "slice-map.md"
$zipPath = Join-Path $outAbs "delta-slice.zip"

$meta | ConvertTo-Json -Depth 5 | Out-File -FilePath $metaPath -Encoding utf8
WriteNdjson $manifestPath $items
if ($WriteMap)
{
    MiniMap $mapPath $items
}

ZipFiles $zipPath $rootAbs $items

Write-Host "OK delta slice:"
Write-Host " - $zipPath"
Write-Host " - $metaPath"
Write-Host " - $manifestPath"
if ($WriteMap)
{
    Write-Host " - $mapPath"
}
