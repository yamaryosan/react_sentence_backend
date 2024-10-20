#!/bin/sh
set -e

# .envファイルが存在しない場合、.env.exampleをコピー
if [ ! -f .env ]; then
    cp .env.example .env
fi

# アプリケーションキーが設定されていない場合、生成
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
    echo "Application key generated"
fi

# マイグレーションの実行(すでにマイグレーションが存在する場合は実行しない)
if [ $(find database/migrations -name "*_create_users_table.php" | wc -l) -eq 0 ]; then
    php artisan migrate
    echo "Migrations executed"
else
    echo "Migrations already exist"
fi

# キャッシュのクリア
php artisan config:cache
php artisan cache:clear
php artisan route:cache
php artisan view:cache

# コンテナのエントリーポイントとしてコマンドを実行
exec "$@"
