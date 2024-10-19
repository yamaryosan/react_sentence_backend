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

# マイグレーションの実行（オプション）
php artisan migrate --force

# コンテナのエントリーポイントとしてコマンドを実行
exec "$@"
