# qiita-stocker-backend
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
