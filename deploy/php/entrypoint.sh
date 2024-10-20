#!/bin/sh
set -e

# .envファイルが存在しない場合、.env.exampleをコピー
if [ ! -f .env ]; then
    cp .env.example .env
fi

# アプリケーションキーが設定されていない場合、生成
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# セッションテーブルの作成
if [ ! -f database/migrations/*_create_sessions_table.php ]; then
    php artisan session:table
else
    echo "Sessions table already exists"
fi

# マイグレーションの実行
php artisan migrate --force

# キャッシュのクリア
php artisan config:cache
php artisan cache:clear
php artisan route:cache
php artisan view:cache

# コンテナのエントリーポイントとしてコマンドを実行
exec "$@"
