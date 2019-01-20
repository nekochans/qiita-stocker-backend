#!/usr/bin/env bash

if [ "$1" != 'dev' ] && [ "$1" != 'stg' ] && [ "$1" != 'prod' ];
then
  echo "Invalid argument! Please set one of 'dev' or 'stg' or 'prod'."
  exit 1
fi

deployStage=$1

dateTime=`date +%Y%m%d_%H%M%S`

aws deploy push \
--region ap-northeast-1 \
--application-name ${deployStage}-api \
--s3-location s3://${deployStage}-qiita-stocker-api-deploy/${dateTime}.zip \
--source ./
