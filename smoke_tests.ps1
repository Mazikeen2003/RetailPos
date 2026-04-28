$base = 'http://127.0.0.1:8000'
Write-Host "== API SMOKE TESTS START =="

Write-Host "Logging in as Administrator (angela@example.com)"
$loginBody = @{ email='angela@example.com'; password='1234' } | ConvertTo-Json
try {
  $login = Invoke-RestMethod -Uri "$base/api/login" -Method Post -Body $loginBody -ContentType 'application/json' -Headers @{ Accept = 'application/json' }
} catch {
  Write-Error "Login failed: $_"
  exit 1
}

if (-not $login.token) {
  Write-Error "Login did not return token: $($login | ConvertTo-Json -Compress)"
  exit 1
}
$token = $login.token
Write-Host "Token received (len) = $($token.Length)"

Write-Host "Creating test product..."
$prodBody = @{ name='Smoke Test Product'; barcode='smoke-0001'; category='Testing'; price=19.9; stock=10 } | ConvertTo-Json
$prod = Invoke-RestMethod -Uri "$base/api/products" -Method Post -Body $prodBody -ContentType 'application/json' -Headers @{ Authorization = "Bearer $token" }
Write-Host "Product created: id=$($prod.id) name=$($prod.name)"

Write-Host "Creating sale with the product..."
$salePayload = @{ items = @(@{ id = $prod.id; qty = 2; price = 19.9 }) } | ConvertTo-Json
$sale = Invoke-RestMethod -Uri "$base/api/sales" -Method Post -Body $salePayload -ContentType 'application/json' -Headers @{ Authorization = "Bearer $token" }
Write-Host "Sale created: id=$($sale.id)"
Write-Host "Sale item id: $($sale.items[0].id) product_id: $($sale.items[0].product_id) qty: $($sale.items[0].qty)"

Write-Host "Voiding sale item..."
$void = Invoke-RestMethod -Uri "$base/api/sales/void-item" -Method Post -Body (@{ sale_id = $sale.id; sale_item_id = $sale.items[0].id } | ConvertTo-Json) -ContentType 'application/json' -Headers @{ Authorization = "Bearer $token" }
Write-Host "Void response: $($void | ConvertTo-Json -Compress)"

Write-Host "Reprinting receipt..."
$reprint = Invoke-RestMethod -Uri "$base/api/receipts/$($sale.id)/reprint" -Method Post -Headers @{ Authorization = "Bearer $token" }
Write-Host "Reprint response: id=$($reprint.id) reprinted=$($reprint.reprinted)"

Write-Host "Posting post-void (as Administrator)..."
$post = Invoke-RestMethod -Uri "$base/api/sales/post-void" -Method Post -Body (@{ sale_id = $sale.id; reason = 'Smoke test reverse' } | ConvertTo-Json) -ContentType 'application/json' -Headers @{ Authorization = "Bearer $token" }
Write-Host "Post-void response: $($post | ConvertTo-Json -Compress)"

Write-Host "Fetching recent audit logs..."
$logs = Invoke-RestMethod -Uri "$base/api/audit-logs" -Headers @{ Authorization = "Bearer $token" }
Write-Host "Audit logs returned: $($logs.Count)"
$logs | Select-Object -First 8 | ForEach-Object { Write-Host "[$($_.created_at)] $($_.action) - $($_.user) - $($_.details)" }

Write-Host "== API SMOKE TESTS COMPLETE =="
