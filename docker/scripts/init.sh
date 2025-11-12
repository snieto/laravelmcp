#!/bin/bash

# TaskMaster AI - Initialization Script
# This script runs when the container first starts

set -e

echo "========================================="
echo "TaskMaster AI - Container Initialization"
echo "========================================="

# Wait for PostgreSQL to be ready
echo "Waiting for PostgreSQL..."
until pg_isready -h postgres -p 5432 -U taskmaster; do
  echo "PostgreSQL is unavailable - sleeping"
  sleep 2
done
echo "✓ PostgreSQL is ready"

# Wait for Redis to be ready
echo "Waiting for Redis..."
until redis-cli -h redis ping; do
  echo "Redis is unavailable - sleeping"
  sleep 2
done
echo "✓ Redis is ready"

# Check if .env exists
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo "✓ .env file created"
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate
    echo "✓ Application key generated"
fi

# Install composer dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    echo "✓ Composer dependencies installed"
fi

# Create storage symlink if it doesn't exist
if [ ! -L "public/storage" ]; then
    echo "Creating storage symlink..."
    php artisan storage:link
    echo "✓ Storage symlink created"
fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force
echo "✓ Migrations completed"

# Seed database if empty
TABLE_COUNT=$(php artisan tinker --execute="echo \DB::table('users')->count();" 2>/dev/null || echo "0")
if [ "$TABLE_COUNT" = "0" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
    echo "✓ Database seeded"
else
    echo "Database already has data, skipping seed"
fi

# Clear and cache configurations
echo "Optimizing Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Laravel optimized"

# Set proper permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
echo "✓ Permissions set"

echo "========================================="
echo "✓ Initialization complete!"
echo "========================================="
echo ""
echo "Application URL: http://localhost:8000"
echo "Adminer URL: http://localhost:8080"
echo ""
echo "Default credentials:"
echo "Email: admin@taskmaster.ai"
echo "Password: password"
echo ""
