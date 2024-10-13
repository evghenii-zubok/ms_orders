#!/usr/bin/env bash

if [[ -d "/var/www/deploy/src" ]]
then
  echo "Folder exists"
  # riscriviamo i permessi dei file
  chown -R 1000:www-data /var/www/deploy/src
  chmod -R 775 /var/www/deploy/src/storage/framework/views
  chmod -R 775 /var/www/deploy/src/storage/logs
fi


