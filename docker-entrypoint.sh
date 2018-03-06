#!/usr/bin/env bash

echo Starting server

set -u
set -e

DB_HOST=${SYMFONY__BRIEVENHULP__DATABASE_HOST:-brievenhulp-db.service.consul}
DB_PORT=${SYMFONY__BRIEVENHULP__DATABASE_PORT:-5432}

cat > /srv/web/brievenhulp/app/config/parameters.yml <<EOF
parameters:
   database_host: ${DB_HOST}
   database_port: ${DB_PORT}
   database_name: ${SYMFONY__BRIEVENHULP__DATABASE_NAME}
   database_user: ${SYMFONY__BRIEVENHULP__DATABASE_USER}
   database_password: ${SYMFONY__BRIEVENHULP__DATABASE_PASSWORD}
   mailer_transport: ${SYMFONY__BRIEVENHULP__MAILER_TRANSPORT}
   mailer_host: ${SYMFONY__BRIEVENHULP__MAILER_HOST}
   mailer_user: ${SYMFONY__BRIEVENHULP__MAILER_USER}
   mailer_password: ${SYMFONY__BRIEVENHULP__MAILER_PASSWORD}
   mailer_port: ${SYMFONY__BRIEVENHULP__MAILER_PORT}
   mailer_encryption: ${SYMFONY__BRIEVENHULP__MAILER_ENCRYPTION}
   mail_from: ${SYMFONY__BRIEVENHULP__MAIL_FROM}
   mail_cc: ${SYMFONY__BRIEVENHULP__MAIL_CC}
   auto_login_from_email: ${SYMFONY__BRIEVENHULP__AUTO_LOGIN_FROM_EMAIL}
   retention_policy: ${SYMFONY__BRIEVENHULP__RETENTION_POLICY}
   secret: ${SYMFONY__BRIEVENHULP__SECRET}
   messagebird_accountkey: ${SYMFONY__BRIEVENHULP__MESSAGEBIRD_API_KEY}
   messagebird_enable: ${SYMFONY__BRIEVENHULP__MESSAGEBIRD_ENABLE}
   sms_originator: ${SYMFONY__BRIEVENHULP__SMS_ORGINATOR}
   piwik_site_id: ${SYMFONY__BRIEVENHULP__PIWIK_SITE_ID}
   sms_disable: false
   trusted_proxies:
        - 127.0.0.1
        - 10.0.0.0/8
        - 172.16.0.0/12
        - 192.168.0.0/16
EOF

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