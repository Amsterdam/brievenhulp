FROM nginx:latest
MAINTAINER datapunt.ois@amsterdam.nl

EXPOSE 80

# add dotdeb - not needed in Debian 9
# COPY Docker/dotdeb.list /etc/apt/sources.list.d/dotdeb.list
# RUN wget https://www.dotdeb.org/dotdeb.gpg \
# && apt-key add dotdeb.gpg

# install php packages
RUN apt-get update && apt-get install -y git vim wget cron rsync php7.0-fpm php7.0-intl php7.0-pgsql php7.0-curl php7.0-cli php7.0-gd php7.0-intl php7.0-mbstring php7.0-mcrypt php7.0-opcache php7.0-sqlite3 php7.0-xml php7.0-xsl php7.0-zip php7.0-igbinary php7.0-json php7.0-memcached php7.0-msgpack php7.0-xmlrpc \
 && apt-get -y upgrade && apt-get -y dist-upgrade && apt-get check && apt-get clean

# create basic directory
RUN mkdir -p /srv/web/brievenhulp

# project setup
COPY . /srv/web/brievenhulp
WORKDIR /srv/web
COPY /Docker/parameters.yml /srv/web/brievenhulp/app/config/parameters.yml
COPY /Docker/fix-null-values.php /srv/web/brievenhulp/app/config/fix-null-values.php
RUN wget https://getcomposer.org/composer.phar

# nginx and php setup
COPY Docker/brievenhulp.vhost /etc/nginx/conf.d/brievenhulp.vhost.conf
RUN rm /etc/nginx/conf.d/default.conf \
 && sed -i '/\;listen\.mode\ \=\ 0660/c\listen\.mode=0666' /etc/php/7.0/fpm/pool.d/www.conf \
 && sed -i '/pm.max_children = 5/c\pm.max_children = 20' /etc/php/7.0/fpm/pool.d/www.conf \
 && sed -i '/\;pm\.max_requests\ \=\ 500/c\pm\.max_requests\ \=\ 100' /etc/php/7.0/fpm/pool.d/www.conf \
 && echo "server_tokens off;" > /etc/nginx/conf.d/extra.conf \
 && echo "client_max_body_size 20m;" >> /etc/nginx/conf.d/extra.conf \
 && sed -i '/upload_max_filesize \= 2M/c\upload_max_filesize \= 20M' /etc/php/7.0/fpm/php.ini \
 && sed -i '/post_max_size \= 8M/c\post_max_size \= 21M' /etc/php/7.0/fpm/php.ini \
 && sed -i '/\;date\.timezone \=/c\date.timezone = Europe\/Amsterdam' /etc/php/7.0/fpm/php.ini \
 && sed -i '/\;security\.limit_extensions \= \.php \.php3 \.php4 \.php5 \.php7/c\security\.limit_extensions \= \.php' /etc/php/7.0/fpm/pool.d/www.conf \
 && sed -e 's/;clear_env = no/clear_env = no/' -i /etc/php/7.0/fpm/pool.d/www.conf

RUN php composer.phar install -d brievenhulp/ --prefer-dist --no-scripts

# redirect logging to stderr
#RUN touch /var/log/cron.log \
# && chmod ugo+rwx /var/log/cron.log
# RUN mkdir -p brievenhulp/var/logs \
# && ln -s /dev/stderr brievenhulp/var/logs/dev.log \
# && ln -s /dev/stderr brievenhulp/var/logs/prod.log \
# && ln -s /dev/stderr /var/log/php7.0-fpm.log \
# && ln -s /dev/stdout /var/log/cron.log

# run
COPY docker-entrypoint.sh /docker-entrypoint.sh
CMD /docker-entrypoint.sh

# Cron jobs placed in /var/spool/cron - system wide and /etc/cron.d did not work (?)
COPY Docker/brievenhulp.cron /var/spool/cron/crontabs/root
