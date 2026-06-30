#!/bin/bash
set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "==> Starting SocialBoost AI..."

# Docker start
cd "$PROJECT_DIR"
docker compose up -d
echo "[OK] Docker containers started"

# DNS fix (systemd-resolved reboot er por change kore)
if grep -q "nameserver 127.0.0.53" /etc/resolv.conf 2>/dev/null; then
    sudo sed -i 's/nameserver 127.0.0.53/nameserver 127.0.0.1/' /etc/resolv.conf
    echo "[OK] DNS fixed"
else
    echo "[OK] DNS already correct"
fi

# Apache check
if ! systemctl is-active --quiet apache2; then
    sudo systemctl start apache2
    echo "[OK] Apache started"
else
    echo "[OK] Apache already running"
fi

echo ""
echo "========================================="
echo "  DONE! Visit: http://smm.test"
echo "========================================="
