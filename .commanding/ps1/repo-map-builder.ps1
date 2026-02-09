param(
  [string]$Root = "",
  [string]$OutFile = "repo-map.md",
  [int]$MaxDepth = 12,

  [switch]$MakeZip,
  [string]$ZipFile = "",

  [string[]]$ExcludeDir = @(
    ".git", ".idea", ".vscode", "bin",
    "vendor", "node_modules",
    ".next", "dist", "build", "coverage",
    "var", "cache", "tmp", "temp", ".tmp", ".temp",
    ".cache", ".turbo",
    "logs", "log",
    ".gate", ".gating", ".commanding",
    ".consuming", ".intelligence", ".release", ".smoke", ".canonization", ".dist"
  ),

  [string[]]$ExcludeExt = @(".log", ".tmp", ".cache"),

  # include files in map (default: folders only)
  [switch]$IncludeFiles
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Load-SliceExcludePolicy([string]$repoRoot) {
  $p = Join-Path $repoRoot ".commanding/policy/slice-exclude.json"
  if (!(Test-Path -LiteralPath $p)) { return $null }
  try {
    $raw = Get-Content -LiteralPath $p -Raw -Encoding UTF8
    return ($raw | ConvertFrom-Json)
  } catch {
    throw "Failed to read slice exclude policy at: $p"
  }
}

function Merge-Unique([string[]]$base, [object]$extra) {
  if ($null -eq $extra) { return $base }
  $set = New-Object 'System.Collections.Generic.HashSet[string]'
  foreach ($x in $base) { if (![string]::IsNullOrWhiteSpace($x)) { [void]$set.Add($x) } }
  foreach ($x in $extra) { if (![string]::IsNullOrWhiteSpace($x)) { [void]$set.Add([string]$x) } }
  return @($set)
}


function Find-RepoRoot([string]$startPath) {
  $p = (Resolve-Path -LiteralPath $startPath).Path
  while ($true) {
    $gitDir = Join-Path $p ".git"
    $ghDir  = Join-Path $p ".github"

    if (Test-Path -LiteralPath $gitDir -PathType Container) { return $p }
    if (Test-Path -LiteralPath $ghDir  -PathType Container) { return $p }

    $parent = Split-Path -Parent $p
    if ([string]::IsNullOrWhiteSpace($parent) -or $parent -eq $p) { break }
    $p = $parent
  }
  return $null
}

function Normalize-Root([string]$p) {
  $full = (Resolve-Path -LiteralPath $p).Path
  return $full.TrimEnd('\','/')
}

function Add-ZipEntryWithRetry(
  [System.IO.Compression.ZipArchive]$zip,
  [string]$sourcePath,
  [string]$entryName,
  [int]$retries = 12,
  [int]$delayMs = 250
) {
  for ($i=0; $i -le $retries; $i++) {
    try {
      [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
        $zip, $sourcePath, $entryName, [System.IO.Compression.CompressionLevel]::Optimal
      ) | Out-Null
      return
    }
    catch [System.IO.IOException] {
      if ($i -eq $retries) { throw }
      Start-Sleep -Milliseconds $delayMs
    }
  }
}

# Discover repo root from current location
$repoRoot = Find-RepoRoot (Get-Location).Path
if (-not $repoRoot) {
  throw "Repository root not found: no .git or .github in parent chain."
}

$RootPath = $repoRoot

$policy = Load-SliceExcludePolicy $RootPath
if ($null -ne $policy) {
  if ($null -ne $policy.excludeDir) { $ExcludeDir = Merge-Unique $ExcludeDir $policy.excludeDir }
  if ($null -ne $policy.excludeExt) { $ExcludeExt = Merge-Unique $ExcludeExt $policy.excludeExt }
}

# Component name = repo root folder name
$Component = Split-Path -Leaf $RootPath
if (-not $Component) { $Component = "repo" }

# Root: default to repo root; allow sub-root within repo if provided
if (-not $Root -or [string]::IsNullOrWhiteSpace($Root)) {
  $Root = $RootPath
} elseif (-not [System.IO.Path]::IsPathRooted($Root)) {
  $Root = Join-Path $RootPath $Root
}

$rootPath = Normalize-Root $Root

# OutFile: default to repo root
if (-not $OutFile -or [string]::IsNullOrWhiteSpace($OutFile)) {
  $OutFile = Join-Path $RootPath "repo-map.md"
} elseif (-not [System.IO.Path]::IsPathRooted($OutFile)) {
  $OutFile = Join-Path $RootPath $OutFile
}

# ZipFile: default to "<component>.zip" in repo root
if (-not $ZipFile -or [string]::IsNullOrWhiteSpace($ZipFile)) {
  $ZipFile = Join-Path $RootPath ("{0}.zip" -f $Component)
} elseif (-not [System.IO.Path]::IsPathRooted($ZipFile)) {
  $ZipFile = Join-Path $RootPath $ZipFile
}

$excludeDirSet = New-Object 'System.Collections.Generic.HashSet[string]' ([StringComparer]::OrdinalIgnoreCase)
foreach ($d in $ExcludeDir) { [void]$excludeDirSet.Add($d) }

$excludeExtSet = New-Object 'System.Collections.Generic.HashSet[string]' ([StringComparer]::OrdinalIgnoreCase)
foreach ($e in $ExcludeExt) { [void]$excludeExtSet.Add($e) }

function Is-ExcludedDir([string]$name) {
  return $excludeDirSet.Contains($name)
}

function Is-ExcludedFile([string]$path) {
  $ext = [System.IO.Path]::GetExtension($path)
  if ([string]::IsNullOrWhiteSpace($ext)) { return $false }
  return $excludeExtSet.Contains($ext)
}

function Get-Relative([string]$fullPath) {
  if ($fullPath.StartsWith($rootPath, [StringComparison]::OrdinalIgnoreCase)) {
    $rel = $fullPath.Substring($rootPath.Length).TrimStart('\','/')
    if ([string]::IsNullOrEmpty($rel)) { return "." }
    return $rel -replace '\\','/'
  }
  return $fullPath -replace '\\','/'
}

function Walk([string]$dir, [int]$depth, [System.Collections.Generic.List[string]]$lines) {
  if ($depth -gt $MaxDepth) { return }

  $items = Get-ChildItem -LiteralPath $dir -Force -ErrorAction Stop |
    Where-Object {
      if ($_.PSIsContainer) { return -not (Is-ExcludedDir $_.Name) }
      if (-not $IncludeFiles) { return $false }
      return -not (Is-ExcludedFile $_.FullName)
    } |
    Sort-Object @{Expression={ -not $_.PSIsContainer }}, Name  # folders first

  foreach ($item in $items) {
    $rel = Get-Relative $item.FullName
    $indent = "  " * $depth
    if ($item.PSIsContainer) {
      $lines.Add("$indent$rel/")
      Walk $item.FullName ($depth + 1) $lines
    } else {
      $lines.Add("$indent$rel")
    }
  }
}

# Build map
$lines = New-Object 'System.Collections.Generic.List[string]'
$lines.Add("REPO MAP")
$lines.Add("root: $((Get-Relative $rootPath))")
try {
  $gitRoot = (& git rev-parse --show-toplevel 2>$null).Trim()
  if ($gitRoot) {
    $sha = (& git rev-parse HEAD 2>$null).Trim()
    $branch = (& git rev-parse --abbrev-ref HEAD 2>$null).Trim()
    if ($sha) { $lines.Add("git: $branch @ $sha") }
  }
} catch {}
$lines.Add("generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")")
$lines.Add("excludeDir: $($ExcludeDir -join ', ')")
$lines.Add("excludeExt: $($ExcludeExt -join ', ')")
$lines.Add("maxDepth: $MaxDepth")
$lines.Add("")
$lines.Add("TREE")
$lines.Add(".")

Walk $rootPath 1 $lines

# Write map as UTF-8 without BOM
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllLines($OutFile, $lines, $utf8NoBom)
Write-Host "OK: $OutFile"

# Make zip (same exclusions, same root as map)
if ($MakeZip) {
  Add-Type -AssemblyName System.IO.Compression
  Add-Type -AssemblyName System.IO.Compression.FileSystem

  if (Test-Path -LiteralPath $ZipFile) { Remove-Item -LiteralPath $ZipFile -Force }

  $zip = [System.IO.Compression.ZipFile]::Open($ZipFile, [System.IO.Compression.ZipArchiveMode]::Create)
  try {
    $files = Get-ChildItem -LiteralPath $rootPath -Recurse -Force -File

    foreach ($f in $files) {
      # Never include the target zip itself
      if ($f.FullName -ieq $ZipFile) { continue }

      # Skip excluded directories (match path segments)
      $skip = $false
      foreach ($d in $ExcludeDir) {
        if ([string]::IsNullOrWhiteSpace($d)) { continue }
        if ($f.FullName -match "([\\/])$([regex]::Escape($d))([\\/])") { $skip = $true; break }
      }
      if ($skip) { continue }

      # Skip excluded extensions
      $ext = [System.IO.Path]::GetExtension($f.FullName)
      if ($ext -and ($ExcludeExt -contains $ext)) { continue }

      # Relative entry name (flat-root, no wrapper folder)
      $rel = $f.FullName.Substring($rootPath.Length).TrimStart('\','/')
      $rel = $rel -replace '\\','/'

      Add-ZipEntryWithRetry -zip $zip -sourcePath $f.FullName -entryName $rel
    }
  }
  finally {
    $zip.Dispose()
  }

  Write-Host "OK: $ZipFile"
}
