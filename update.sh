git pull
php ../composer.phar install
php bin/console cache:clear --env=dev
php bin/console cache:clear --env=prod
php bin/console doctrine:migrations:migrate --env=prod
php bin/console assetic:dump
