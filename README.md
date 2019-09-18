# MailHogger

A wrapper around https://github.com/mailhog/MailHog allowing limiting the emails shown in the UI.

## Installation

```sh
git clone https://github.com/rimi-itk/mailhogger
cd mailhogger
docker-compose up --detach
docker-compose exec phpfpm composer install
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```
