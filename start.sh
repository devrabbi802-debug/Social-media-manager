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

# Ngrok tunnel start (Facebook webhook er jonno)
if [ -f "$HOME/ngrok" ]; then
    # Purano ngrok bondho koro
    pkill -f "ngrok http" 2>/dev/null || true
    sleep 1

    # Ngrok start koro
    nohup "$HOME/ngrok" http 8000 > /tmp/ngrok.log 2>&1 &
    sleep 4

    NGROK_URL=$(wget -qO- http://127.0.0.1:4040/api/tunnels 2>/dev/null | grep -o '"public_url":"[^"]*"' | head -1 | cut -d'"' -f4)

    if [ -n "$NGROK_URL" ]; then
        echo ""
        echo "========================================="
        echo "  NGROK TUNNEL ACTIVE"
        echo "  Webhook URL: ${NGROK_URL}/webhook/facebook"
        echo "========================================="
    else
        echo "[WARN] Ngrok start hoyni — manually chalao: ~/ngrok http 8000"
    fi
else
    echo "[WARN] Ngrok install nai. Install: snap install ngrok"
fi
