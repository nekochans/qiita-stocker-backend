# qiita-stocker-backend
[![CircleCI](https://circleci.com/gh/nekochans/qiita-stocker-backend.svg?style=svg)](https://circleci.com/gh/nekochans/qiita-stocker-backend)

## 環境変数の設定

1. `.env`、`.env.testing`ファイルを作成します。

2. 以下の環境変数を設定します。

```
//.env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qiita_stocker
DB_USERNAME=qiita_stocker
DB_PASSWORD=(YourPassword999)
CORS_ORIGIN=http://localhost:8080
MAINTENANCE_MODE=false

//.env.testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qiita_stocker_test
DB_USERNAME=qiita_stocker_test
DB_PASSWORD=(YourPassword999)
CORS_ORIGIN=http://localhost:8080
MAINTENANCE_MODE=false
```

## DockerfileのBuildを行いECRにプッシュする

### ローカル環境からECRにDockerイメージをプッシュする

`push-ecr-local.sh` を実行して下さい。

例えば利用しているAWSアカウントが `000000000000` で Dockerイメージに `1.0.0` のタグを付けたい場合は以下のように実行します。

```bash
./push-ecr-local.sh 000000000000 1.0.0
```

### CodeBuildプロジェクトからECRにDockerイメージをプッシュする

`buildspec-push-ecr.yml` が実行されます。

CodeBuildの定義自体は [こちら](https://github.com/nekochans/qiita-stocker-terraform/blob/master/modules/aws/api/codebuild.tf) に定義されています。

一時的な動作確認時には `push-ecr-local.sh` を使い、本番反映する際にはこちらのCodeBuildプロジェクトのほうを利用するのが良いでしょう。

## CircleCIをローカル上で実行する

以下の手順を実行するとCircleCIがローカル上で実行出来るようになります。

CircleCI自体がDockerを使うので、Docker上ではなくあくまでもMac上で実行する必要があります。

詳しい手順はこちらを見て下さい。

https://circleci.com/docs/2.0/local-cli/

依存packageとしてDocker for Macが入ってきますが、大抵の場合、既にインストール済みだと思うので以下を実行すれば良いでしょう。

```bash
brew install --ignore-dependencies circleci
```

プロジェクトルートで以下を実行するとBuildが実行されます。

```bash
circleci build
```
