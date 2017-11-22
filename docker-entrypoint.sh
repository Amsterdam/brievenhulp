#!/usr/bin/env bash

echo Starting server

set -u
set -e

# Already in Dockerfile (?)
php composer.phar install -d brievenhulp/

php brievenhulp/bin/console assetic:dump --env=prod
php brievenhulp/bin/console cache:clear --env=prod
php brievenhulp/bin/console doctrine:query:sql "CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";" --env=prod
php brievenhulp/bin/console doctrine:migrations:migrate -n --env=prod
chown -R www-data:www-data brievenhulp/var/ && find brievenhulp/var/ -type d chmod -R 0770 {} \; && find brievenhulp/var/ -type f chmod -R 0660 {} \;

service cron start
service php7.0-fpm start
nginx -g "daemon off;"