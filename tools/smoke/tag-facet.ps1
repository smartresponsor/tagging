Param([string]$BaseUrl=$env:TAG_BASE_URL)
if (-not $BaseUrl) { Write-Error "Set TAG_BASE_URL"; exit 1 }
Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body (@{slug="facet-a"; label="Facet A"} | ConvertTo-Json) -ContentType "application/json" | Out-Null
Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag" -Body (@{slug="facet-b"; label="Facet B"} | ConvertTo-Json) -ContentType "application/json" | Out-Null
$ta = (Invoke-RestMethod -Method Get -Uri "$BaseUrl/tag?query=facet-a").items[0]
$tb = (Invoke-RestMethod -Method Get -Uri "$BaseUrl/tag?query=facet-b").items[0]
Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag/assign" -Body (@{tagId=$ta.id; assignedType="product"; assignedId="1"} | ConvertTo-Json) -ContentType "application/json" | Out-Null
Invoke-RestMethod -Method Post -Uri "$BaseUrl/tag/assign" -Body (@{tagId=$tb.id; assignedType="product"; assignedId="1"} | ConvertTo-Json) -ContentType "application/json" | Out-Null
$facet = Invoke-RestMethod -Method Get -Uri "$BaseUrl/tag/facet?type=product&limit=10"
$cloud = Invoke-RestMethod -Method Get -Uri "$BaseUrl/tag/cloud?limit=10"
"{0} / {1}" -f $facet.items.Count, $cloud.items.Count | Write-Host
