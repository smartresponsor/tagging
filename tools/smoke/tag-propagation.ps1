Param([string]$BaseUrl = $env:TAG_BASE_URL)
if (-not $BaseUrl)
{
    Write-Error "Set TAG_BASE_URL"; exit 1
}
$t = Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body (@{ slug = "pii"; label = "PII" } | ConvertTo-Json) -ContentType "application/json"
Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag/{0}/classify" -f $t.id -Body (@{ key = "data-class"; value = "PII" } | ConvertTo-Json) -ContentType "application/json" | Out-Null
Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag/assign" -Body (@{ tagId = $t.id; assignedType = "product"; assignedId = "42" } | ConvertTo-Json) -ContentType "application/json" | Out-Null
$r = Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag/{0}/replay" -f $t.id
"Applied: {0}" -f $r.applied | Write-Host
