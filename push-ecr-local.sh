#!/usr/bin/env bash

if [[ "$1" = "" ]]; then
  echo  "AWSアカウントIDを第1引数に指定して下さい"
  exit 1
fi

if [[ "$2" = "" ]]; then
  echo  "imageTagを第2引数に指定してください"
  exit 1
fi

awsAccountId="$1"
imageTag="$2"
nginxRepositoryName=stg-api-nginx
phpRepositoryName=stg-api-php

$(aws ecr get-login --no-include-email --region ap-northeast-1 --profile qiita-stocker-dev)

docker build -t ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:latest -f docker/nginx/Dockerfile .
docker tag ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:latest ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:${imageTag}
docker push ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:latest
docker push ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:${imageTag}

docker build -t ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:latest -f docker/php/Dockerfile .
docker tag ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:latest ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:${imageTag}
docker push ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:latest
docker push ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:${imageTag}
