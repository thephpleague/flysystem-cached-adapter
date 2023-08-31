#!/bin/sh
docker compose exec -it -e XDEBUG_MODE=off php php -d memory_limit=256M vendor/bin/phpstan analyze "$@"
