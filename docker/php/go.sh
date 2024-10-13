#!/usr/bin/env bash

cd /
perms.sh
service cron start
service apache2 start
cd /var/www/deploy/src
nohup php artisan queue:work --daemon >> /var/www/deploy/src/storage/logs/laravel.log &
tail -f /var/log/apache2/error.log
