Param([string]$BaseUrl = $env:TAG_BASE_URL)
if (-not $BaseUrl)
{
    Write-Error "Set TAG_BASE_URL"
    exit 1
}

$policyBody = @{ deniedPrefixes = @("bad-") } | ConvertTo-Json
$createBody = @{ slug = "bad-test"; label = "Bad" } | ConvertTo-Json

Invoke-RestMethod -Method Put -Uri "$BaseUrl/tag/policy" -Body $policyBody -ContentType "application/json"
try
{
    Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body $createBody -ContentType "application/json" | Out-Null
    Write-Error "Policy not enforced"
    exit 2
}
catch
{
    Write-Host "Policy enforced OK"
}
$r = Invoke-RestMethod -Method Get -Uri "$BaseUrl/tag/policy/report"
if ($r.violations -eq $null)
{
    Write-Error "No report"
    exit 3
}
Write-Host "Audit entries: $( $r.violations.Count )"
