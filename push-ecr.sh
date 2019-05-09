#!/usr/bin/env bash

awsAccountId=${AWS_ACCOUNT_ID}
imageTag=${IMAGE_TAG}
deployStage=${DEPLOY_STAGE}
nginxRepositoryName="${deployStage}-api-nginx"
phpRepositoryName="${deployStage}-api-php"

if [[ "$deployStage" != "stg" ]] && [[ "$deployStage" != "prod" ]]; then
  echo  "有効なステージではありません。stg, prod が利用出来ます"
  exit 1
fi

if [[ "$awsAccountId" = "" ]]; then
  echo  "AWS_ACCOUNT_IDにAWSアカウントIDを設定して下さい"
  exit 1
fi

if [[ "$imageTag" = "" ]]; then
  echo  "IMAGE_TAGにimageTagを設定して下さい"
  exit 1
fi

$(aws ecr get-login --no-include-email --region ap-northeast-1)

docker build -t ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:latest -f docker/nginx/Dockerfile .
docker tag ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:latest ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:${imageTag}
docker push ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:latest
docker push ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${nginxRepositoryName}:${imageTag}

docker build -t ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:latest -f docker/php/Dockerfile .
docker tag ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:latest ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:${imageTag}
docker push ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:latest
docker push ${awsAccountId}.dkr.ecr.ap-northeast-1.amazonaws.com/${phpRepositoryName}:${imageTag}
