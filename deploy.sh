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

if [[ -f package-lock.json ]] && command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
elif [[ -f package.json ]] && command -v npm >/dev/null 2>&1; then
  npm install --no-audit --no-fund
  npm run build
fi

log "Deploy finished successfully"