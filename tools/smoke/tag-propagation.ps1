Param([string]$BaseUrl = $env:TAG_BASE_URL)
if (-not $BaseUrl)
{
    Write-Error "Set TAG_BASE_URL"
    exit 1
}

$createBody = @{ slug = "pii"; label = "PII" } | ConvertTo-Json
$classifyBody = @{ key = "data-class"; value = "PII" } | ConvertTo-Json
$assignBody = @{
    tagId = $null
    assignedType = "product"
    assignedId = "42"
}

$t = Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body $createBody -ContentType "application/json"
$classifyUri = "{0}/tag/{1}/classify" -f $BaseUrl, $t.id
Invoke-RestMethod -Method Post -Uri $classifyUri -Body $classifyBody -ContentType "application/json" | Out-Null
$assignBody.tagId = $t.id
Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag/assign" -Body ($assignBody | ConvertTo-Json) -ContentType "application/json" | Out-Null
$r = Invoke-RestMethod -Method Post -Uri ("{0}/tag/{1}/replay" -f $BaseUrl, $t.id)
"Applied: {0}" -f $r.applied | Write-Host
