#!/bin/bash
set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "==> Starting SocialBoost AI..."

# Docker start
cd "$PROJECT_DIR"
docker compose up -d
echo "[OK] Docker containers started"

# CLIP Server start (Python FastAPI)
CLIP_DIR="$PROJECT_DIR/clip-server"
if [ -d "$CLIP_DIR" ]; then
    # Check if CLIP server is already running
    if ! pgrep -f "python.*main.py" > /dev/null 2>&1; then
        echo "[INFO] Starting CLIP Image Recognition Server..."
        cd "$CLIP_DIR"
        
        # Check if venv exists, if not create it
        if [ ! -d "venv" ]; then
            echo "[INFO] Creating Python virtual environment..."
            python3 -m venv venv
            source venv/bin/activate
            pip install -r requirements.txt
        else
            source venv/bin/activate
        fi
        
        # Start CLIP server in background
        nohup python main.py > /tmp/clip-server.log 2>&1 &
        CLIP_PID=$!
        
        # Wait for server to start
        sleep 5
        
        if kill -0 $CLIP_PID 2>/dev/null; then
            echo "[OK] CLIP Server started (PID: $CLIP_PID)"
        else
            echo "[WARN] CLIP Server start hoyni. Check /tmp/clip-server.log"
        fi
        
        cd "$PROJECT_DIR"
    else
        echo "[OK] CLIP Server already running"
    fi
else
    echo "[WARN] CLIP Server directory not found"
fi

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

# Storefront auto-build
STOREFRONT_DIR="$PROJECT_DIR/resources/storefront"
if [ -d "$STOREFRONT_DIR" ]; then
    if ! pgrep -f "vite build --watch" > /dev/null 2>&1; then
        echo "[INFO] Storefront auto-build starting..."
        cd "$STOREFRONT_DIR"
        nohup npx vite build --watch > /tmp/storefront-build.log 2>&1 &
        cd "$PROJECT_DIR"
        echo "[OK] Storefront auto-build started"
    else
        echo "[OK] Storefront auto-build already running"
    fi
fi

echo ""
echo "========================================="
echo "  DONE! Visit: http://smm.test"
echo "========================================="
echo ""
echo "  CLIP Server: http://localhost:8089"
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
        # Save ngrok URL to file (PHP app reads this for webhook registration)
        echo "$NGROK_URL" > "$PROJECT_DIR/.ngrok-url"

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
