[CmdletBinding()]
param()

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
Push-Location $root
try {
    $content = (& git show HEAD:src/Service/Config/TaggingPolicyConfigService.php) -join "`n"
    if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrWhiteSpace($content)) {
        throw 'Unable to restore TaggingPolicyConfigService from HEAD.'
    }
    $content = $content.Replace('ConfigApplyService', 'AdministrationConfigApplyService')
    $content = $content.Replace('ConfigFileWriterService', 'AdministrationConfigFileWriterService')
    $target = Join-Path $root 'src/Service/Config/TaggingPolicyConfigService.php'
    [System.IO.File]::WriteAllText($target, $content + "`n", [System.Text.UTF8Encoding]::new($false))
}
finally { Pop-Location }
