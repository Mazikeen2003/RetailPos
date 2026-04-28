# Developer Setup & Run Guide

This document explains how to set up and run the backend (Laravel) and frontend (React) for the RetailPos workspace.

## Prerequisites
- PHP 8.2+ with extensions required by Laravel
- Composer
- Node.js (16+) and npm
- SQLite or other DB (project uses database/database.sqlite by default)
- Git

---

## Backend (Laravel)

From `backend/laravel`:

Fast local reset/setup from the repository root:

```powershell
.\setup_local.ps1 -Fresh
```

Use this when a teammate sees errors like `no such table: roles`; it recreates the local SQLite database, runs migrations, and seeds demo users/products.

1. Install PHP deps:

```bash
cd backend/laravel
composer install
```

2. Copy env and generate key:

```bash
copy .env.example .env    # Windows (PowerShell: Copy-Item .env.example .env)
php artisan key:generate
```

3. If using SQLite (recommended for local dev):

```bash
# create empty sqlite file
mkdir -p database
type nul > database\database.sqlite
# ensure .env contains DB_CONNECTION=sqlite and DB_DATABASE=database/database.sqlite
```

4. Run migrations & seeders:

```bash
php artisan migrate --seed
```

5. (Optional) Publish Sanctum migration (if not already done):

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force
php artisan migrate
```

6. Start local dev server:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

7. Useful commands:

```bash
# run tests
vendor/bin/phpunit
# run PHPStan (static analysis)
vendor/bin/phpstan analyse --configuration=phpstan.neon --level=5 --memory-limit=1G
```

---

## Frontend (React)

From workspace root `frontend/`:

```bash
cd frontend
npm install
# fix issues where possible (non-breaking):
npm audit fix
# build production bundle
npm run build
# or run dev server
npm start
```

If you host the frontend separately, set the API base URL via environment or by configuring the axios client in `frontend/src/api/axios.js`.

---

## Quick Smoke Test

The repo includes a script `smoke_tests.ps1` that exercises login, sale creation, voiding, reprint, and post-void flows. Run it from `backend/laravel` in PowerShell:

```powershell
cd backend/laravel
powershell -ExecutionPolicy Bypass -File .\smoke_tests.ps1
```

---

## Notes & Troubleshooting

- If you see Sanctum token errors, ensure migrations ran and `personal_access_tokens` exists.
- If PHPStan complains about model properties, ensure models have phpdoc annotations and typed relation return types (we added these to `app/Models`).
- If frontend build fails with `vite` or `react-scripts` not found, run `npm install` in the correct `frontend/` directory.

---

## Contact
If you want, I can: run the smoke tests, open a PR, or continue with additional frontend polish and tests.
