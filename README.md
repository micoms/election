# Student Council Election (Database-Backed)

This project now uses **PHP + MySQL** with role-based access:
- **Admin**: can view election metrics and live vote tally in `/pages/admin-dashboard.php`
- **Voter**: can register, log in, vote per position, and submit a final ballot

## Setup

1. Create the MySQL schema and seed data:
   ```sql
   SOURCE /absolute/path/to/database/schema.sql;
   ```
2. Configure DB connection using environment variables (optional):
   - `DB_HOST` (default `127.0.0.1`)
   - `DB_PORT` (default `3306`)
   - `DB_NAME` (default `election`)
   - `DB_USER` (default `root`)
   - `DB_PASS` (default empty)
3. Run with a PHP server:
   ```bash
   php -S 127.0.0.1:8000
   ```
4. Open `http://127.0.0.1:8000/index.php`

## Default admin account

- Student ID: `ADMIN001`
- Password: `admin123`
