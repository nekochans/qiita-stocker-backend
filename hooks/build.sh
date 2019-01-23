#!/usr/bin/env bash

source /home/ec2-user/.bash_profile

cd /home/ec2-user/qiita-stocker-backend

composer install --optimize-autoloader --no-dev

php artisan cache:clear

php artisan config:cache

php artisan route:cache
