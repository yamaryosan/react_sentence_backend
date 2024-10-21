#!/bin/sh
set -e

# .env.exampleをコピー
cp .env.example .env

# アプリケーションキーが設定されていない場合、生成
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
    echo "Application key generated"
fi

# 環境変数の確認
echo "Current environment variables(before cache clear):"
env | grep -E '^(APP_KEY|DB_|AWS_|MEILISEARCH_|SCOUT_|NG_WORDS|UPLOAD_PERMISSION_USERNAME|LOCK_KEYWORD|UNLOCK_KEYWORD|UPLOAD_SESSION_KEY|SENTENCE_SESSION_KEY)'

# キャッシュのクリア
php artisan config:clear
php artisan cache:clear

# 環境変数の確認
echo "Current environment variables(after cache clear):"
env | grep -E '^(APP_KEY|DB_|AWS_|MEILISEARCH_|SCOUT_|NG_WORDS|UPLOAD_PERMISSION_USERNAME|LOCK_KEYWORD|UNLOCK_KEYWORD|UPLOAD_SESSION_KEY|SENTENCE_SESSION_KEY)'

# マイグレーションの実行
php artisan migrate --force
echo "Migrations executed"

# デバッグ用 ,.envの内容を表示
echo "Current environment variables:"
cat .env

# コンテナのエントリーポイントとしてコマンドを実行
exec "$@"
