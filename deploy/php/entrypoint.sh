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

# マイグレーションの実行
php artisan migrate
echo "Migrations executed"

# キャッシュのクリア
php artisan config:cache
php artisan cache:clear
php artisan route:cache
php artisan view:cache

# コンテナのエントリーポイントとしてコマンドを実行
exec "$@"
