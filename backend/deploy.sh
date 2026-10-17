#!/usr/bin/env bash
# Laravel Forge deploy script. Paste this into the site's "Deploy script" editor
# (or call `bash backend/deploy.sh`). Site settings required in Forge:
#   - Web directory: backend/public
#   - Zero downtime deployments: off
#   - .env symlink on the server (once): ln -s ../.env backend/.env
set -e

cd "$FORGE_SITE_PATH"
git pull origin "$FORGE_SITE_BRANCH"

cd backend

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

npm ci
npm run build

# SQLite database file must exist before migrating
mkdir -p database
touch database/database.sqlite

$FORGE_PHP artisan migrate --force
$FORGE_PHP artisan optimize:clear
$FORGE_PHP artisan optimize
