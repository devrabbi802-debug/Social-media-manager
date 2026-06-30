#!/bin/bash
set -e

DOMAIN="smm.test"
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "==> Setting up $DOMAIN..."

# 1. /etc/hosts e add koro (skip if already exists)
if ! grep -q "$DOMAIN" /etc/hosts; then
    echo "127.0.0.1 $DOMAIN" >> /etc/hosts
    echo "[OK] $DOMAIN added to /etc/hosts"
else
    echo "[OK] $DOMAIN already in /etc/hosts"
fi

# 2. dnsmasq install & configure koro (*.smm.test -> 127.0.0.1)
if ! command -v dnsmasq &>/dev/null; then
    apt-get install -y dnsmasq >/dev/null 2>&1
    echo "[OK] dnsmasq installed"
fi

if [ ! -f /etc/dnsmasq.d/smm.test.conf ]; then
    echo "address=/$DOMAIN/127.0.0.1" > /etc/dnsmasq.d/smm.test.conf
    echo "[OK] dnsmasq wildcard config created: *.$DOMAIN -> 127.0.0.1"
else
    echo "[OK] dnsmasq config already exists"
fi

# dnsmasq restart koro
systemctl restart dnsmasq 2>/dev/null || systemctl start dnsmasq 2>/dev/null
echo "[OK] dnsmasq restarted"

# /etc/resolv.conf update koro (local DNS resolve korte)
if ! grep -q "nameserver 127.0.0.1" /etc/resolv.conf; then
    echo "[INFO] Add 'nameserver 127.0.0.1' to /etc/resolv.conf for local DNS resolution"
    echo "       Or run: sudo sed -i '1i nameserver 127.0.0.1' /etc/resolv.conf"
fi

# 3. Apache proxy modules enable koro
a2enmod proxy proxy_http rewrite >/dev/null 2>&1
echo "[OK] Apache proxy modules enabled"

# 4. Wildcard Apache VirtualHost create koro
cat > /etc/apache2/sites-available/$DOMAIN.conf << VHOST
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAlias *.$DOMAIN

    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:8000/
    ProxyPassReverse / http://127.0.0.1:8000/

    ErrorLog \${APACHE_LOG_DIR}/${DOMAIN}-error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}-access.log combined
</VirtualHost>
VHOST
echo "[OK] Apache wildcard VirtualHost created"

# 5. VirtualHost enable koro
a2ensite $DOMAIN.conf >/dev/null 2>&1
# Old single-domain config remove koro jodi thake
a2dissite smm-wildcard.conf >/dev/null 2>&1 || true
echo "[OK] Apache site enabled"

# 6. Apache restart koro
systemctl restart apache2
echo "[OK] Apache restarted"

# 7. .env e APP_URL & APP_DOMAIN update koro
if [ -f .env ]; then
    sed -i "s|APP_URL=.*|APP_URL=http://$DOMAIN|" .env
    sed -i "s|APP_DOMAIN=.*|APP_DOMAIN=$DOMAIN|" .env
    if ! grep -q "APP_DOMAIN" .env; then
        sed -i "/APP_URL=.*/a APP_DOMAIN=$DOMAIN" .env
    fi
    echo "[OK] .env updated"
fi

# 8. Docker containers start koro
cd "$PROJECT_DIR"
docker compose up -d
echo "[OK] Docker containers started"

echo ""
echo "========================================="
echo "  DONE! Visit: http://$DOMAIN"
echo "  Wildcard: *.$DOMAIN -> 127.0.0.1"
echo "========================================="
