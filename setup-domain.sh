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

# 2. Apache proxy modules enable koro
a2enmod proxy proxy_http rewrite >/dev/null 2>&1
echo "[OK] Apache proxy modules enabled"

# 3. Apache VirtualHost create koro
cat > /etc/apache2/sites-available/$DOMAIN.conf << VHOST
<VirtualHost *:80>
    ServerName $DOMAIN

    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:8000/
    ProxyPassReverse / http://127.0.0.1:8000/

    ErrorLog \${APACHE_LOG_DIR}/${DOMAIN}-error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}-access.log combined
</VirtualHost>
VHOST
echo "[OK] Apache VirtualHost created"

# 4. VirtualHost enable koro
a2ensite $DOMAIN.conf >/dev/null 2>&1
echo "[OK] Apache site enabled"

# 5. Apache restart koro
systemctl restart apache2
echo "[OK] Apache restarted"

# 6. Docker containers start koro
cd "$PROJECT_DIR"

# 7. .env e APP_URL update koro
if [ -f .env ]; then
    sed -i "s|APP_URL=.*|APP_URL=http://$DOMAIN|" .env
    echo "[OK] APP_URL updated in .env"
fi

# 8. Docker containers start koro
docker compose up -d
echo "[OK] Docker containers started"

echo ""
echo "========================================="
echo "  DONE! Visit: http://$DOMAIN"
echo "========================================="
