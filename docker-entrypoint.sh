#!/bin/bash
set -e

echo "Waiting for MySQL to be ready..."
until php -r "
try {
    new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    echo 'MySQL connected!' . PHP_EOL;
    exit(0);
} catch (PDOException \$e) {
    echo 'Waiting for MySQL... (' . \$e->getMessage() . ')' . PHP_EOL;
    exit(1);
}
" 2>/dev/null; do
    echo "MySQL is not ready yet, retrying in 2s..."
    sleep 2
done

if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

if [ ! -d "node_modules" ]; then
    echo "Installing Node dependencies..."
    npm install
fi

if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

echo "Running database migrations..."
php artisan migrate --force

echo "Starting Laravel development server..."
exec php artisan serve --host=0.0.0.0 --port=8000
