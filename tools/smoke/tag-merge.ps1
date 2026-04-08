Param([string]$BaseUrl = $env:TAG_BASE_URL)
if (-not $BaseUrl)
{
    Write-Error "Set TAG_BASE_URL"
    exit 1
}

$createA = @{ slug = "merge-a"; label = "Merge A" } | ConvertTo-Json
$createB = @{ slug = "merge-b"; label = "Merge B" } | ConvertTo-Json
$mergeBody = @{
    fromTagId = $null
    toTagId = $null
}

$a = Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body $createA -ContentType "application/json"
$b = Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body $createB -ContentType "application/json"
$mergeBody.fromTagId = $a.id
$mergeBody.toTagId = $b.id
Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag/merge" -Body ($mergeBody | ConvertTo-Json) -ContentType "application/json"
Write-Host "Merged $( $a.id ) -> $( $b.id )"
