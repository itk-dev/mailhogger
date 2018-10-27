# MailHogger

A wrapper around https://github.com/mailhog/MailHog allowing limiting the emails shown in the UI.

## Installation

```sh
git clone https://github.com/rimi-itk/mailhogger
cd mailhogger
SYMFONY_ENV=prod composer install --no-dev --optimize-autoloader
bin/console
bin/console doctrine:migrations:migrate  --no-interaction
```
