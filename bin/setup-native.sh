#!/usr/bin/env bash
#
# ALDAPCON Platform — first-run setup, native Ubuntu (no Docker)
#
# TRD §10: "If Docker is unfamiliar territory, plain PHP-FPM on the host is
# entirely legitimate here and one less thing to debug during an incident."
# This is that path.
#
# Target: Ubuntu 24.04 LTS, which ships the TRD §14 stack from its own
# repositories — PHP 8.3, PostgreSQL 16, Redis 7, nginx — with no third-party
# PPAs and no version pinning.
#
# Deliberately close to what Phase 17 has to do on the Lagos VPS. Everything
# below that touches the server rather than the application should end up in
# the provisioning playbook, so that rebuilding after a data-centre failure is
# a rerun rather than an act of memory.
#
# Run from the repository root:  ./bin/setup-native.sh
# Idempotent — running it twice is safe.

set -euo pipefail

cd "$(dirname "$0")/.."

say()  { printf '\n\033[1;32m==>\033[0m %s\n' "$1"; }
warn() { printf '\033[1;33m    !\033[0m %s\n' "$1"; }
die()  { printf '\n\033[1;31mError:\033[0m %s\n' "$1" >&2; exit 1; }

DB_NAME="${DB_DATABASE:-aldapcon}"
DB_TEST_NAME="aldapcon_test"
DB_USER="${DB_USERNAME:-aldapcon}"
DB_PASS="${DB_PASSWORD:-secret}"

# ---------------------------------------------------------------------------
# Preconditions
# ---------------------------------------------------------------------------
command -v apt-get >/dev/null 2>&1 || die "This script targets Debian/Ubuntu. See README.md."

if [ "$(id -u)" -eq 0 ]; then
    SUDO=""
else
    command -v sudo >/dev/null 2>&1 || die "sudo is required, or run this as root."
    SUDO="sudo"
fi

# ---------------------------------------------------------------------------
# System packages
# ---------------------------------------------------------------------------
say "Installing system packages"
export DEBIAN_FRONTEND=noninteractive
$SUDO apt-get update -qq
$SUDO apt-get install -y -qq --no-install-recommends \
    php8.3-cli php8.3-fpm \
    php8.3-pgsql php8.3-redis php8.3-intl php8.3-zip php8.3-gd \
    php8.3-bcmath php8.3-mbstring php8.3-xml php8.3-curl php8.3-opcache \
    postgresql-16 postgresql-contrib-16 \
    redis-server \
    nginx \
    git curl unzip ca-certificates

php -r 'exit(version_compare(PHP_VERSION, "8.3", ">=") ? 0 : 1);' \
    || die "PHP 8.3+ required (TRD §14), found $(php -r 'echo PHP_VERSION;')"

# ---------------------------------------------------------------------------
# Composer
# ---------------------------------------------------------------------------
if ! command -v composer >/dev/null 2>&1; then
    say "Installing Composer"
    EXPECTED="$(curl -fsSL https://composer.github.io/installer.sig)"
    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    ACTUAL="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")"
    # The installer is fetched over the network and then executed. Verifying it
    # is not ceremony; an unverified installer is a supply-chain hole in a
    # payment application.
    [ "$EXPECTED" = "$ACTUAL" ] || die "Composer installer checksum mismatch — aborting."
    $SUDO php /tmp/composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
else
    echo "    Composer already installed"
fi

# ---------------------------------------------------------------------------
# Node — needed for Vite. Ubuntu 24.04 ships Node 18; Vite 6 wants 20+.
# ---------------------------------------------------------------------------
say "Ensuring Node.js 20 or newer"
if ! command -v node >/dev/null 2>&1 || [ "$(node -p 'process.versions.node.split(".")[0]')" -lt 20 ]; then
    curl -fsSL https://deb.nodesource.com/setup_22.x | $SUDO -E bash -
    $SUDO apt-get install -y -qq nodejs
else
    echo "    Node $(node -v) is fine"
fi

# ---------------------------------------------------------------------------
# Services
#
# WSL has no systemd by default, so services are started directly rather than
# through systemctl. On the Lagos VPS these are systemd units (TRD §10).
# ---------------------------------------------------------------------------
say "Starting PostgreSQL and Redis"
# Detect systemd by looking for its runtime directory rather than by calling
# systemctl. In a devcontainer systemctl is a stub that lectures about systemd
# not running before it fails, which reads like an error in the middle of a
# setup script when it is merely a fact about containers.
if [ -d /run/systemd/system ]; then
    $SUDO systemctl enable --now postgresql redis-server
else
    echo "    No systemd (container) — using service"
    $SUDO service postgresql start  >/dev/null 2>&1 || true
    $SUDO service redis-server start >/dev/null 2>&1 || true
fi

for _ in $(seq 1 30); do
    $SUDO -u postgres psql -c 'SELECT 1' >/dev/null 2>&1 && break
    sleep 1
done
$SUDO -u postgres psql -c 'SELECT 1' >/dev/null 2>&1 \
    || die "PostgreSQL did not start. Try: sudo service postgresql start"

redis-cli ping >/dev/null 2>&1 || die "Redis did not start. Try: sudo service redis-server start"

# ---------------------------------------------------------------------------
# Database
#
# Two databases: the development one, and aldapcon_test for the suite.
# RefreshDatabase truncates whatever it is pointed at, so they must be separate.
# ---------------------------------------------------------------------------
say "Creating the database role and databases"

$SUDO -u postgres psql -v ON_ERROR_STOP=1 <<SQL >/dev/null
DO \$\$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = '${DB_USER}') THEN
        CREATE ROLE ${DB_USER} LOGIN PASSWORD '${DB_PASS}';
    END IF;
END
\$\$;
SQL

for db in "${DB_NAME}" "${DB_TEST_NAME}"; do
    exists="$($SUDO -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='${db}'")"
    if [ "$exists" != "1" ]; then
        # Nigerian names must survive CSV export to Excel (AC-F9). UTF-8 from
        # the first byte written, never retrofitted.
        $SUDO -u postgres createdb -O "${DB_USER}" -E UTF8 "${db}"
        echo "    Created ${db}"
    else
        echo "    ${db} already exists"
    fi
done

$SUDO -u postgres psql -d "${DB_NAME}"      -c "GRANT ALL ON SCHEMA public TO ${DB_USER}" >/dev/null
$SUDO -u postgres psql -d "${DB_TEST_NAME}" -c "GRANT ALL ON SCHEMA public TO ${DB_USER}" >/dev/null

# ---------------------------------------------------------------------------
# Application
# ---------------------------------------------------------------------------
say "Preparing .env"
if [ ! -f .env ]; then
    cp .env.example .env
    # .env.example points at the Docker service names. Native runs on
    # loopback, and PostgreSQL and Redis are bound there only (TRD §6.2).
    sed -i \
        -e 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' \
        -e 's/^REDIS_HOST=.*/REDIS_HOST=127.0.0.1/' \
        -e 's/^MAIL_HOST=.*/MAIL_HOST=127.0.0.1/' \
        .env
    echo "    Created .env from .env.example, pointed at 127.0.0.1"
else
    echo "    .env already exists, leaving it alone"
fi

say "Installing PHP dependencies"
composer install --no-interaction --prefer-dist

say "Generating the application key"
# Only when absent. Regenerating APP_KEY on an existing install makes every
# encrypted column and every session unreadable (TRD §6.4 treats it as a
# rotation procedure, not a routine one).
if grep -qE '^APP_KEY=$' .env; then
    php artisan key:generate
else
    echo "    APP_KEY already set, leaving it alone"
fi

say "Installing frontend dependencies and building assets"
npm install
npm run build

say "Running migrations"
php artisan migrate --no-interaction

say "Verifying the environment"
php artisan aldapcon:verify-environment

cat <<'EOF'

  Setup complete.

  Start the application (three processes, matching the production topology):

    php artisan serve --host=0.0.0.0 --port=8000    # web
    php artisan queue:work --tries=3 --backoff=5    # worker
    php artisan schedule:work                       # scheduler

  Run the checks:

    php artisan test
    ./vendor/bin/pint --test
    ./vendor/bin/phpstan analyse

  Mail is not configured natively. Either install Mailpit
  (https://mailpit.axllent.org) or set MAIL_MAILER=log in .env — Phase 7
  replaces this once B-5 is answered.

EOF
