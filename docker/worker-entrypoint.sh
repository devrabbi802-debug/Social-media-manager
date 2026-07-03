#!/bin/bash
set -e

# Install Supervisor
apt-get update && apt-get install -y supervisor && rm -rf /var/lib/apt/lists/*

mkdir -p /var/log/supervisor

# Copy supervisor config
cp /var/www/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

echo "Starting queue workers via Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
