#!/bin/sh
docker compose exec -it php vendor/bin/phpunit "$@"
