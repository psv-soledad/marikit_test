#!/bin/bash
./create_env.sh

docker-compose up --build --force-recreate -d
echo "Sleeping for 5 seconds…"
sleep 5
docker exec -ti php-fpm sh -c "composer install"
docker exec -ti php-fpm sh -c "mysql job < /home/job/dump.sql"
docker exec -ti php-fpm sh -c "php console.php parse"
docker-compose stop
echo "Для запуска: docker-compose up -d"