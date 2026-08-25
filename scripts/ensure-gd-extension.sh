#!/usr/bin/env bash
set -euo pipefail

# Check if mua_app has GD extension loaded
if docker exec mua_app php -m 2>/dev/null | grep -qi '^gd$'; then
    echo "[ensure-gd] GD extension is already active in mua_app."
    exit 0
fi

echo "[ensure-gd] GD extension not found in mua_app. Injecting GD bundle..."

# Create bundle on host if not exists
if [ ! -f /tmp/gd_bundle/gd.so ]; then
    python3 -c '
import subprocess, os, re, shutil

def get_deps(binary_path):
    output = subprocess.check_output(["ldd", binary_path]).decode()
    deps = []
    for line in output.splitlines():
        m = re.search(r"=>\s+([/\w\.\-]+)", line)
        if m:
            deps.append(m.group(1))
    return deps

all_libs = set()
to_scan = ["/usr/lib/php/20240924/gd.so"]
scanned = set()

while to_scan:
    item = to_scan.pop(0)
    if item in scanned or not os.path.exists(item):
        continue
    scanned.add(item)
    deps = get_deps(item)
    for d in deps:
        if d.startswith("/lib") or d.startswith("/usr/lib"):
            if not d.startswith("/lib/x86_64-linux-gnu/libc.so") and not d.startswith("/lib64/"):
                if os.path.exists(d):
                    all_libs.add(d)
                    if d not in scanned:
                        to_scan.append(d)

os.makedirs("/tmp/gd_bundle", exist_ok=True)
shutil.copy2("/usr/lib/php/20240924/gd.so", "/tmp/gd_bundle/gd.so")
for lib in all_libs:
    shutil.copy2(lib, f"/tmp/gd_bundle/{os.path.basename(lib)}")
'
fi

# Inject into running containers
for c in mua_app mua_worker mua_scheduler; do
    if docker ps --format '{{.Names}}' | grep -q "^${c}$"; then
        echo "[ensure-gd] Injecting into container: $c"
        docker cp /tmp/gd_bundle/gd.so "${c}:/usr/local/lib/php/extensions/no-debug-non-zts-20240924/gd.so"
        docker cp /tmp/gd_bundle/. "${c}:/lib/x86_64-linux-gnu/"
        docker exec -u 0 "$c" sh -c "echo 'extension=gd' > /usr/local/etc/php/conf.d/docker-php-ext-gd.ini"
    fi
done

docker restart mua_app mua_worker mua_scheduler >/dev/null 2>&1 || true
echo "[ensure-gd] GD extension successfully installed and containers restarted."
