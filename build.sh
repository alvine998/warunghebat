#!/bin/bash

set -e

echo "======================================"
echo " Laravel Deployment"
echo "======================================"

echo ""
echo "[1/7] Installing Composer dependencies..."
composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader

echo ""
echo "[2/7] Clearing Laravel cache..."
php artisan optimize:clear

echo ""
echo "[3/7] Running database migrations..."
php artisan migrate --force

echo ""
echo "[4/7] Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo ""
echo "[5/7] Optimizing Laravel..."
php artisan optimize

echo ""
echo "[6/7] Building frontend assets..."
npm run build

echo ""
echo "[7/7] Deployment completed!"

echo ""
echo "======================================"
echo " SUCCESS"
echo "======================================"