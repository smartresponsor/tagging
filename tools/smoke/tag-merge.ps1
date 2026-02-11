Param([string]$BaseUrl = $env:TAG_BASE_URL)
if (-not $BaseUrl)
{
    Write-Error "Set TAG_BASE_URL"; exit 1
}
$a = Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body (@{ slug = "merge-a"; label = "Merge A" } | ConvertTo-Json) -ContentType "application/json"
$b = Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body (@{ slug = "merge-b"; label = "Merge B" } | ConvertTo-Json) -ContentType "application/json"
Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag/merge" -Body (@{ fromTagId = $a.id; toTagId = $b.id } | ConvertTo-Json) -ContentType "application/json"
Write-Host "Merged $( $a.id ) -> $( $b.id )"
