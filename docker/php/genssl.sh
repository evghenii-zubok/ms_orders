#!/usr/bin/env bash

if [[ "$APP_ENV" == "dev" ]]
then
  echo "-- APP_ENV is: $APP_ENV. Will try to generate self-signed certificates."
  echo "-- VIRTUAL_HOST found: $VIRTUAL_HOST. Going to split them."
  IFS="," read -a domains_array <<< "$VIRTUAL_HOST"
  for i in "${domains_array[@]}"
  do
     echo "Generating keys for $i"
     openssl req -x509 -nodes -days 365 -subj "/C=IT/ST=Veneto/L=Affi/O=4service/OU=WebDevs/CN=4service" -newkey rsa:2048 -keyout /etc/apache2/certs/"$i".key -out /etc/apache2/certs/"$i".crt
  done
  echo "-- All certs was generated. :)"
else
  echo "Application environment is: $APP_ENV. Skipping self-signed certificates generation."
fi

CRON_FILE="/etc/cron.d/consolecron"
if [[ -f "$CRON_FILE" && -s "$CRON_FILE" ]]; then
  echo "$CRON_FILE found. Exporting ENV VAR"
  echo "APP_ENV is: $APP_ENV"

  # Set APP_ENV consolecron
  sed -i -e "s#__APP_ENV__#$APP_ENV#g" /etc/cron.d/consolecron

else
    echo "$CRON_FILE - Not exists."
fi

exec "$@"
