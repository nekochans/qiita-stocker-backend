#!/usr/bin/env bash

source /home/ec2-user/.bash_profile

cd /home/ec2-user/qiita-stocker-backend

php artisan migrate
