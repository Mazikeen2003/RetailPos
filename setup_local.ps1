param(
    [switch]$Fresh
)

$ErrorActionPreference = "Stop"

$backend = Join-Path $PSScriptRoot "backend\laravel"
$envPath = Join-Path $backend ".env"
$envExamplePath = Join-Path $backend ".env.example"
$databaseDir = Join-Path $backend "database"
$sqlitePath = Join-Path $databaseDir "database.sqlite"

Set-Location $backend

if (!(Test-Path $envPath)) {
    Copy-Item $envExamplePath $envPath
    Write-Host "Created backend/laravel/.env from .env.example"
}

if (!(Test-Path $databaseDir)) {
    New-Item -ItemType Directory -Path $databaseDir | Out-Null
}

if (!(Test-Path $sqlitePath)) {
    New-Item -ItemType File -Path $sqlitePath | Out-Null
    Write-Host "Created backend/laravel/database/database.sqlite"
}

if (!(Test-Path (Join-Path $backend "vendor\autoload.php"))) {
    composer install
}

$envText = Get-Content $envPath -Raw
if ($envText -notmatch "APP_KEY=base64:") {
    php artisan key:generate
}

php artisan config:clear

if ($Fresh) {
    php artisan migrate:fresh --seed
} else {
    php artisan migrate --seed
}

Write-Host ""
Write-Host "Local database is ready."
Write-Host "Demo logins:"
Write-Host "  Maria Cruz / 1234        (Cashier)"
Write-Host "  Daniel Reyes / 1234      (Supervisor)"
Write-Host "  Angela Santos / 1234     (Admin)"
