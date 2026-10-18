#!/usr/bin/env bash
# Laravel Forge deploy script. Paste this into the site's "Deploy script" editor
# (or call `bash deploy.sh`). Site settings required in Forge:
#   - Web directory: public
#   - Zero downtime deployments: off
set -e

cd "$FORGE_SITE_PATH"
git pull origin "$FORGE_SITE_BRANCH"

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

npm ci
npm run build

# SQLite database file must exist before migrating
mkdir -p database
touch database/database.sqlite

$FORGE_PHP artisan migrate --force
$FORGE_PHP artisan optimize:clear
$FORGE_PHP artisan optimize
