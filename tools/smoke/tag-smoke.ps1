Param([string]$BaseUrl=$env:TAG_BASE_URL)
if (-not $BaseUrl) { Write-Error "Set TAG_BASE_URL or pass -BaseUrl"; exit 1 }
$slug = "demo-" + (Get-Random)
$resp = Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body (@{slug=$slug; label="Demo"} | ConvertTo-Json) -ContentType "application/json"
if (-not $resp.id) { Write-Error "Create failed"; exit 2 }
Write-Host "Created tag $($resp.slug) id=$($resp.id)"
$list = Invoke-RestMethod -Method Get -Uri "$BaseUrl/tag?query=demo&limit=5"
Write-Host "List items: $($list.items.Count)"
exit 0
