-- 'qiita_stocker' というユーザー名のユーザーを '(YourPassword999)' というパスワードで作成
-- データベース 'qiita_stocker' への権限を付与
CREATE DATABASE qiita_stocker;
CREATE USER qiita_stocker@localhost IDENTIFIED WITH mysql_native_password BY '(YourPassword999)';
GRANT ALL ON qiita_stocker.* TO 'qiita_stocker'@'localhost';

-- 'qiita_stocker_test' というユーザー名のユーザーを '(YourPassword999)' というパスワードで作成
-- データベース 'qiita_stocker_test' への権限を付与
CREATE DATABASE qiita_stocker_test;
CREATE USER qiita_stocker_test@localhost IDENTIFIED WITH mysql_native_password BY '(YourPassword999)';
GRANT ALL ON qiita_stocker_test.* TO 'qiita_stocker_test'@'localhost';
