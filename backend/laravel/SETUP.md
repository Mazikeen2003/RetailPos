Local setup (Windows) — Laravel + MySQL

1) Create MySQL database and user (run in MySQL shell or MySQL Workbench):

```sql
CREATE DATABASE retailpos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'retail'@'localhost' IDENTIFIED BY 'your_password_here';
GRANT ALL PRIVILEGES ON retailpos.* TO 'retail'@'localhost';
FLUSH PRIVILEGES;
```

2) Update Laravel environment variables

- Open `backend/laravel/.env` and set:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=retailpos
DB_USERNAME=retail
DB_PASSWORD=your_password_here
```

3) Install dependencies, publish Sanctum migrations, migrate and seed

```powershell
cd backend/laravel
composer install
php artisan key:generate
# publish Sanctum migration if needed
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate --seed

# (optional) reset DB and seed fresh
php artisan migrate:fresh --seed
```

4) Start backend server

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

5) Frontend (from repo root)

```powershell
cd frontend
npm install
# point the frontend to the API (optional, env var)
$env:REACT_APP_API_URL = "http://127.0.0.1:8000/api"
npm start
```

Notes
- If you see `no such table: personal_access_tokens` errors, run the migrations step again.
- The project ships with `database/migrations` including sessions and sanctum tables. Ensure `php artisan migrate` completes successfully.
