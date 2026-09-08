#!/usr/bin/env bash
#
# ALDAPCON Platform — first-run setup
#
# Plan Phase 1 completion criterion: a second machine can clone the repo,
# follow the README, and reach a working local environment in under 15 minutes.
# This script is that path. It is idempotent — running it twice is safe.

set -euo pipefail

cd "$(dirname "$0")/.."

say() { printf '\n\033[1;32m==>\033[0m %s\n' "$1"; }
die() { printf '\n\033[1;31mError:\033[0m %s\n' "$1" >&2; exit 1; }

command -v docker >/dev/null 2>&1 || die "Docker is not installed. See README.md."
docker compose version >/dev/null 2>&1 || die "Docker Compose v2 is required."
docker info >/dev/null 2>&1 || die "The Docker daemon is not running. Start Docker Desktop and retry."

say "Preparing .env"
if [ ! -f .env ]; then
    cp .env.example .env
    echo "    Created .env from .env.example"
else
    echo "    .env already exists, leaving it alone"
fi

say "Building the application image"
docker compose build

say "Starting PostgreSQL and Redis"
docker compose up -d postgres redis

say "Waiting for PostgreSQL and Redis to report healthy"
for _ in $(seq 1 60); do
    pg=$(docker compose ps --format json postgres 2>/dev/null | grep -o '"Health":"[a-z]*"' | head -1 || true)
    rd=$(docker compose ps --format json redis 2>/dev/null | grep -o '"Health":"[a-z]*"' | head -1 || true)
    if [ "$pg" = '"Health":"healthy"' ] && [ "$rd" = '"Health":"healthy"' ]; then
        echo "    Both healthy"
        break
    fi
    sleep 2
done

say "Installing PHP dependencies"
docker compose run --rm --no-deps app composer install --no-interaction --prefer-dist

say "Generating the application key"
# Only when absent — regenerating APP_KEY on an existing install makes every
# encrypted column and every session unreadable (TRD §6.4 lists it as a
# rotation procedure, not a routine one).
if grep -qE '^APP_KEY=$' .env; then
    docker compose run --rm --no-deps app php artisan key:generate
else
    echo "    APP_KEY already set, leaving it alone"
fi

say "Installing frontend dependencies and building assets"
docker compose run --rm --no-deps app npm install
docker compose run --rm --no-deps app npm run build

say "Creating the test database"
# Tests run against aldapcon_test (phpunit.xml), never the development
# database — RefreshDatabase truncates whatever it is pointed at.
docker compose exec -T postgres \
    psql -U "${DB_USERNAME:-aldapcon}" -d postgres \
    -c "SELECT 'CREATE DATABASE aldapcon_test' WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'aldapcon_test')\gexec" \
    >/dev/null

say "Running migrations"
docker compose run --rm app php artisan migrate --no-interaction

say "Starting the full stack"
docker compose up -d

say "Verifying the environment"
docker compose run --rm app php artisan aldapcon:verify-environment

cat <<'EOF'

  Setup complete.

    Application   http://localhost:8000
    Mail (Mailpit) http://localhost:8025

    Run the test suite   docker compose run --rm app php artisan test
    Format               docker compose run --rm app ./vendor/bin/pint
    Static analysis      docker compose run --rm app ./vendor/bin/phpstan analyse
    Stop everything      docker compose down

EOF
