FROM registry.gitlab.com/thecodeine-docker/php:7.0-fpm

ARG SYMFONY_ENV
ENV SYMFONY_ENV $SYMFONY_ENV
ENV CUSTOM_PHP_FPM_PATH /usr/local/etc/php-fpm.d/zzz-custom.conf

RUN echo -e "always_populate_raw_post_data =-1" >> $CUSTOM_PHP_INI_PATH \
	&& echo -e "cgi.fix_pathinfo = 1" >> $CUSTOM_PHP_INI_PATH

RUN mkdir -p /usr/local/etc/php-fpm.d \
	&& echo -e "pm.max_children = 30" >> $CUSTOM_PHP_FPM_PATH \
	&& echo -e "pm.min_spare_servers = 2" >> $CUSTOM_PHP_FPM_PATH \
	&& echo -e "pm.max_spare_servers = 5" >> $CUSTOM_PHP_FPM_PATH \
	&& echo -e "pm.max_requests = 50" >> $CUSTOM_PHP_FPM_PATH

RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

COPY docker-php-entrypoint.sh /
COPY mautic.crontab /var/spool/cron/crontabs/www-data

ENTRYPOINT ["/docker-php-entrypoint.sh"]

CMD ["php-fpm"]