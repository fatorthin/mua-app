#!/usr/bin/env bash

set -euo pipefail

# -----------------------------
# Webhook deploy configuration
# -----------------------------
APP_DIR="${APP_DIR:-/DATA/AppData/mua-app}"
BRANCH="${DEPLOY_BRANCH:-main}"
WEBHOOK_SECRET="${WEBHOOK_SECRET:-}"
LOG_FILE="${DEPLOY_LOG_FILE:-$APP_DIR/storage/logs/deploy.log}"
DEPLOY_USE_DOCKER="${DEPLOY_USE_DOCKER:-auto}"
FALLBACK_LOG_FILE="${DEPLOY_FALLBACK_LOG_FILE:-$APP_DIR/deploy.log}"

timestamp() {
  date '+%Y-%m-%d %H:%M:%S'
}

log() {
  local msg="$1"
  echo "[$(timestamp)] $msg" | tee -a "$LOG_FILE"
}

fail() {
  local msg="$1"
  log "ERROR: $msg"
  exit 1
}

ensure_command() {
  local cmd="$1"
  command -v "$cmd" >/dev/null 2>&1 || fail "Command not found: $cmd"
}

run_in_app_container() {
  local cmd="$1"
  docker compose exec -T app sh -lc "$cmd"
}

run_in_app_container_as_root() {
  local cmd="$1"
  docker compose exec -T -u root app sh -lc "$cmd"
}

resolve_host_npm() {
  if command -v npm >/dev/null 2>&1; then
    command -v npm
    return 0
  fi

  local nvm_dir
  nvm_dir="${NVM_DIR:-$HOME/.nvm}"

  if [[ -s "$nvm_dir/nvm.sh" ]]; then
    # shellcheck disable=SC1090
    . "$nvm_dir/nvm.sh" >/dev/null 2>&1 || true
  fi

  if command -v npm >/dev/null 2>&1; then
    command -v npm
    return 0
  fi

  local npm_candidates
  npm_candidates="$(ls -1d "$nvm_dir"/versions/node/*/bin/npm 2>/dev/null || true)"
  if [[ -n "$npm_candidates" ]]; then
    echo "$npm_candidates" | sort -V | tail -n 1
    return 0
  fi

  return 1
}

prepare_log_file() {
  local preferred_dir

  preferred_dir="$(dirname "$LOG_FILE")"
  mkdir -p "$preferred_dir" 2>/dev/null || true

  if [[ -e "$LOG_FILE" && -w "$LOG_FILE" ]]; then
    return
  fi

  if [[ ! -e "$LOG_FILE" && -w "$preferred_dir" ]]; then
    touch "$LOG_FILE"
    return
  fi

  LOG_FILE="$FALLBACK_LOG_FILE"
  mkdir -p "$(dirname "$LOG_FILE")"
  touch "$LOG_FILE"
}

prepare_log_file

on_error() {
  local exit_code="$1"
  local line_no="$2"
  local cmd="$3"
  log "ERROR: Deploy failed (exit $exit_code) at line $line_no while running: $cmd"
}

trap 'on_error "$?" "$LINENO" "$BASH_COMMAND"' ERR

PAYLOAD_FILE="$(mktemp)"
cleanup() {
  rm -f "$PAYLOAD_FILE"
}
trap cleanup EXIT

cat > "$PAYLOAD_FILE"

if [[ -n "$WEBHOOK_SECRET" ]]; then
  ensure_command openssl

  sig_from_header="${HTTP_X_HUB_SIGNATURE_256:-${X_HUB_SIGNATURE_256:-}}"
  [[ -n "$sig_from_header" ]] || fail "Missing X-Hub-Signature-256 header"

  computed_hash="$(openssl dgst -sha256 -hmac "$WEBHOOK_SECRET" "$PAYLOAD_FILE" | awk '{print $2}')"
  expected_sig="sha256=$computed_hash"

  if [[ "$expected_sig" != "$sig_from_header" ]]; then
    fail "Invalid webhook signature"
  fi
fi

payload_ref=""
if command -v jq >/dev/null 2>&1; then
  payload_ref="$(jq -r '.ref // empty' "$PAYLOAD_FILE")"
else
  payload_ref="$(grep -oE '"ref"\s*:\s*"[^"]+"' "$PAYLOAD_FILE" | head -n 1 | sed -E 's/^"ref"\s*:\s*"([^"]+)"$/\1/')"
fi

if [[ -n "$payload_ref" && "$payload_ref" != "refs/heads/$BRANCH" ]]; then
  log "Ignored webhook for ref '$payload_ref' (target: refs/heads/$BRANCH)"
  exit 0
fi

ensure_command git

[[ -d "$APP_DIR/.git" ]] || fail "Not a git repository: $APP_DIR"

cd "$APP_DIR"

use_docker="false"
if [[ "$DEPLOY_USE_DOCKER" == "true" ]]; then
  use_docker="true"
elif [[ "$DEPLOY_USE_DOCKER" == "auto" && -f "$APP_DIR/docker-compose.yml" ]]; then
  use_docker="true"
fi

if [[ "$use_docker" == "true" ]]; then
  ensure_command docker
else
  ensure_command php
  ensure_command composer
fi

log "Deploy started for branch $BRANCH"

git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

if [[ "$use_docker" == "true" ]]; then
  log "Running deploy commands inside docker service 'app'"

  # Ensure app user can update dependencies and caches on bind-mounted project files.
  run_in_app_container_as_root "chown -R www-data:www-data /var/www/html/vendor /var/www/html/storage /var/www/html/bootstrap/cache || true"

  bash "$APP_DIR/scripts/ensure-gd-extension.sh" || true

  run_in_app_container "composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev"
  run_in_app_container "php artisan migrate --force"
  run_in_app_container "php artisan optimize:clear"
  run_in_app_container "php artisan config:cache"
  run_in_app_container "php artisan route:cache || true"
  run_in_app_container "php artisan view:cache"
else
  composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

  php artisan migrate --force
  php artisan optimize:clear
  php artisan config:cache
  php artisan route:cache || true
  php artisan view:cache
fi

if [[ -f package.json ]]; then
  host_npm="$(resolve_host_npm || true)"

  if [[ "$use_docker" == "true" ]]; then
    if run_in_app_container "command -v npm >/dev/null 2>&1"; then
      log "Running frontend build inside docker service 'app'"

      if [[ -f package-lock.json ]]; then
        run_in_app_container "npm ci --no-audit --no-fund"
      else
        run_in_app_container "npm install --no-audit --no-fund"
      fi

      run_in_app_container "npm run build"
    elif [[ -n "$host_npm" ]]; then
      log "npm not found in container; running frontend build on host via $host_npm"

      # Ensure host user owns node_modules & public/build if docker previously touched them
      run_in_app_container_as_root "chown -R $(id -u):$(id -g) /var/www/html/node_modules /var/www/html/public/build 2>/dev/null || true"

      if [[ -f package-lock.json ]]; then
        "$host_npm" ci --no-audit --no-fund
      else
        "$host_npm" install --no-audit --no-fund
      fi

      "$host_npm" run build
    else
      log "Skipping frontend build: npm not found in both container and host"
    fi
  elif [[ -n "$host_npm" ]]; then
    log "Running frontend build on host via $host_npm"

    if [[ -f package-lock.json ]]; then
      "$host_npm" ci --no-audit --no-fund
    else
      "$host_npm" install --no-audit --no-fund
    fi

    "$host_npm" run build
  else
    log "Skipping frontend build: npm not found on host"
  fi
else
  log "Skipping frontend build: package.json not found"
fi

log "Deploy finished successfully"