#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$REPO_ROOT"

if [[ ! -f package.json ]]; then
  echo "No package.json found; skipping npm build."
  exit 0
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "npm is not installed; skipping build."
  exit 0
fi

echo "[build-on-pull] Installing frontend dependencies..."
if [[ -f package-lock.json ]]; then
  npm ci --no-audit --no-fund
else
  npm install --no-audit --no-fund
fi

echo "[build-on-pull] Running npm run build..."
npm run build

echo "[build-on-pull] Frontend build completed successfully."
