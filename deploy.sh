#!/bin/bash

set -e

echo "======================================"
echo " Laravel Deployment"
echo "======================================"

APP_DIR="/home/warunghe/warunghebat"

cd "$APP_DIR"

echo ""
echo "[1/7] Pull latest code..."
git pull

echo ""
echo "[2/7] Installing Composer dependencies..."
composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader

echo ""
echo "[3/7] Clearing Laravel cache..."
php artisan optimize:clear

echo ""
echo "[4/7] Running database migrations..."
php artisan migrate --force

echo ""
echo "[5/7] Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo ""
echo "[6/7] Optimizing Laravel..."
php artisan optimize

echo ""
echo "[7/7] Deployment completed!"

echo ""
echo "======================================"
echo " SUCCESS"
echo "======================================"