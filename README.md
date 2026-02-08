# Work Progress Management (PHP OOP)

A complete PHP OOP task management system for **Workers** and **Admins** with responsive Bootstrap UI.

## Features

### Workers
- Login with credentials
- View assigned tasks
- Click **Start Work** to begin daily work
- Upload start image
- Upload end image and submit progress at end of day

### Admins
- Login as admin
- View all sites and progress
- Manage users (create workers), sites, and tasks
- Generate reports via CSV export (Excel-compatible)
- Import task data from CSV/Excel-exported CSV

### Security
- Password hashing (`password_hash`, bcrypt)
- SQL injection prevention (PDO prepared statements)
- XSS protection (`htmlspecialchars` escape helper)
- File upload validation (mime type + size + random filename)
- Session security (HTTPOnly + SameSite cookies, strict mode, session regeneration)
- CSRF protection (token for all forms)
- Task ownership and state checks for worker actions

## Stack
- PHP 8+
- SQLite
- Bootstrap 5

## Local setup

```bash
php scripts/setup.php
php -S 0.0.0.0:8000 -t public
```

Open: `http://localhost:8000`

## Container setup (target container)

```bash
docker compose up --build
```

Open: `http://localhost:8080`

Then initialize database once:

```bash
docker compose exec app php scripts/setup.php
```

### Default users
- Admin: `admin / admin123`
- Worker: `worker1 / worker123`

## CSV import format

```csv
title,description,user_id,site_id
```

## Project structure
- `app/Controllers` – request handlers
- `app/Models` – DB interaction
- `app/Core` – core helpers
- `app/Views` – templates
- `public/index.php` – front controller/router
- `scripts/setup.php` – DB migration + seed
