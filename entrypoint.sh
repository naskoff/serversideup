#!/bin/sh

set -e

php "$APP_BASE_DIR/bin/console" cache:clear
echo "✅ Clear cache run successful."

if [ -d "$APP_BASE_DIR/migrations" ]; then
    php "$APP_BASE_DIR/bin/console" doctrine:migrations:migrate --no-interaction
    echo "✅ Database migrations run successful."
fi