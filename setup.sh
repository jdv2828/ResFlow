#!/usr/bin/env bash
set -euo pipefail

echo "=== FuelPass Setup ==="

# Copy .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env created"
fi

# Generate app key
php artisan key:generate --force
echo "✅ APP_KEY generated"

# Create SQLite database
touch database/database.sqlite
echo "✅ SQLite database file created"

# Run migrations and seeders
php artisan migrate --force --seed
echo "✅ Database migrated and seeded"

echo ""
echo "=== FuelPass is ready! ==="
echo "Run: php artisan serve"
echo "Login: admin@admin.com / secret"
