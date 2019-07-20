FROM php:7.2-fpm

# Обновляем и ставим необходимые пакеты
RUN apt-get update && apt-get -y upgrade && apt-get install -y --no-install-recommends \
    unzip zip git nano mysql-client

#Ставим расширения
RUN docker-php-ext-install -j$(nproc) mysqli pdo pdo_mysql

# Обновляем PECL
RUN pecl channel-update pecl.php.net

# Ставим redis и xdebug
RUN pecl install -o -f xdebug \
    &&  rm -rf /tmp/pear

# composer
ENV PATH "/composer/vendor/bin:$PATH"
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer
ENV COMPOSER_VERSION 1.8.0

RUN curl --silent --fail --location --retry 3 --output /tmp/installer.php --url https://getcomposer.org/installer \
 && php -r " \
    \$signature = '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5'; \
    \$hash = hash('sha384', file_get_contents('/tmp/installer.php')); \
    if (!hash_equals(\$signature, \$hash)) { \
        unlink('/tmp/installer.php'); \
        echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
        exit(1); \
    }" \
 && php /tmp/installer.php --no-ansi --install-dir=/usr/bin --filename=composer --version=${COMPOSER_VERSION} \
 && composer --ansi --version --no-interaction \
 && rm -rf /tmp/* /tmp/.htaccess

#Пользователь
ARG USER_UID
ARG USER_GID
RUN export uid=$USER_UID gid=$USER_GID && \
    groupadd -r job -g ${gid} && \
    useradd -u ${uid} -r -g job -m -d /home/job -s /sbin/nologin -c "Docker image user" job

RUN mkdir /var/www/job && \
    chown -R job:job /var/www/job && \
    chown -R job:job /home/job && \
    chown -R job:job /composer

RUN usermod -a -G www-data job

ADD ./docker/contaners/php-fpm/script/my.cnf /home/job/.my.cnf
ADD ./docker/contaners/php-fpm/script/sql/dump.sql /home/job/dump.sql

USER job
