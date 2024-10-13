FROM php:8.3-apache
# Args passati nella docker compose
ARG APP_ENV
ENV ACCEPT_EULA=Y
# Mantainer
LABEL maintainer="evghenii.zubok@gmail.com"

RUN a2enmod ssl && a2enmod rewrite

RUN apt update && apt upgrade -y && \
    apt install -y vim \
        nano \
        wget \
        apt-utils \
        libxml2-dev \
        gnupg \
        tzdata \
        libzip-dev \
        libpng-dev \
        zlib1g-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libc-client-dev \
        libkrb5-dev \
        ca-certificates \
        openssl \
        libcurl4-openssl-dev \
        zip \
        software-properties-common \
        apt-transport-https \
        mariadb-client \
        cron \
        htop \
        nodejs \
        telnet \
        traceroute \
        libicu-dev \
        libonig-dev \
        curl \
        npm && \
    apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* && \
    echo 'alias ll="ls -al"' >> ~/.bashrc

RUN docker-php-ext-install zip
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mbstring
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install MS ODBC Driver for MSSQL SERVER
RUN curl https://packages.microsoft.com/keys/microsoft.asc | tee /etc/apt/trusted.gpg.d/microsoft.asc
RUN curl https://packages.microsoft.com/config/debian/11/prod.list | tee /etc/apt/sources.list.d/mssql-release.list

RUN apt update && apt install -y unixodbc-dev libgssapi-krb5-2
RUN ACCEPT_EULA=Y apt install -y msodbcsql18

# INSTALL PHP DRIVER FOR MSSQL SERVER
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
RUN apt update

RUN pecl install sqlsrv
RUN pecl install pdo_sqlsrv

RUN printf "; priority=20\nextension=sqlsrv.so\n" > /usr/local/etc/php/conf.d/docker-php-ext-sqlsrv.ini
RUN printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /usr/local/etc/php/conf.d/docker-php-ext-pdo_sqlsrv.ini

RUN docker-php-ext-enable sqlsrv
RUN docker-php-ext-enable pdo_sqlsrv

# Questi 2 sono già così. Questi commandi non modificano nulla, ma mettiamoli
RUN a2dismod mpm_event
RUN a2enmod mpm_prefork

# INSTALL PHPREDIS LIB
RUN pecl install --force redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# INSTALL PCNTL LIB for Laravel Reverb
RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY ./consolecron /etc/cron.d/consolecron
COPY ./genssl.sh /genssl.sh
COPY ./perms.sh /perms.sh
COPY ./go.sh /go.sh

RUN if [[ -f "/etc/cron.d/consolecron" && -s "/etc/cron.d/consolecron" ]]; then sed -i -e "s#__APP_ENV__#$APP_ENV#g" /etc/cron.d/consolecron; fi
RUN chmod +x /genssl.sh && \
    chmod +x /perms.sh && \
    chmod +x /go.sh && \
    chmod 0644 /etc/cron.d/consolecron && \
    crontab /etc/cron.d/consolecron

ENTRYPOINT ["/genssl.sh"]

EXPOSE 80

CMD ["/bin/bash", "-c", "/go.sh"]
