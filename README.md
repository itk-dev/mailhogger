# MailHogger

A wrapper around https://github.com/mailhog/MailHog allowing limiting the emails shown in the UI.

## Installation

```sh
git clone https://github.com/rimi-itk/mailhogger
cd mailhogger
```

```sh
docker-compose pull
docker-compose up --detach
docker-compose exec phpfpm composer install
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```


## Install MailHogger

```sh
mkdir -p /data/www/mailhogger/mailhog/{bin,config,data}
echo '{}' > /data/www/mailhogger/mailhog/outgoing-smtp.json
curl --location https://github.com/mailhog/MailHog/releases/download/v1.0.0/MailHog_linux_amd64 > /data/www/mailhogger/mailhog/bin/MailHog
chmod +x /data/www/mailhogger/mailhog/bin/MailHog

cat <<'EOF' > /etc/systemd/system/mailhog.service
[Unit]
Description=MailHog service
After=network.service

[Service]
ExecStart=/data/www/mailhogger/mailhog/bin/MailHog \
  -api-bind-addr 127.0.0.1:8025 \
  -ui-bind-addr 127.0.0.1:8025 \
  -smtp-bind-addr 127.0.0.1:1025 \
  -outgoing-smtp /data/www/mailhogger/mailhog/outgoing-smtp.json \
  -storage maildir \
  -maildir-path /data/www/mailhogger/mailhog/data

[Install]
WantedBy=multi-user.target
EOF
```


## Send test emails

```sh
docker-compose exec phpfpm bin/console app:email:send test@example.com
```
