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

//.env.testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qiita_stocker_test
DB_USERNAME=qiita_stocker_test
DB_PASSWORD=(YourPassword999)
```
