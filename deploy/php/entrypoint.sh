#!/bin/sh
set -e

# .env.exampleをコピー
cp .env.example .env

# アプリケーションキーが設定されていない場合、生成
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
    echo "Application key generated"
fi

# マイグレーションの実行
php artisan migrate --force
echo "Migrations executed"

# キャッシュのクリア
php artisan config:cache
php artisan config:clear
php artisan cache:clear
php artisan route:cache
php artisan view:cache

# コンテナのエントリーポイントとしてコマンドを実行
exec "$@"
