#!/usr/bin/env bash

source /home/ec2-user/.bash_profile

cd /home/ec2-user/qiita-stocker-backend

php artisan cache:clear

composer dump-autoload --optimize

php artisan clear-compiled

php artisan optimize

php artisan config:cache

php artisan route:cache
