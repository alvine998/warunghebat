#!/bin/bash

set -e

echo "======================================"
echo " Laravel Deployment"
echo "======================================"

echo ""
echo "[1/6] Installing Composer dependencies..."
composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader

echo ""
echo "[2/6] Clearing Laravel cache..."
php artisan optimize:clear

echo ""
echo "[3/6] Running database migrations..."
php artisan migrate --force

echo ""
echo "[4/6] Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo ""
echo "[5/6] Optimizing Laravel..."
php artisan optimize

echo ""
echo "[6/6] Deployment completed!"

echo ""
echo "======================================"
echo " SUCCESS"
echo "======================================"