#!/bin/bash
set -e

# /etc/hostsにエントリを追加
echo "127.0.0.1 mysql" >> /etc/hosts

# MySQLに接続して必要な操作を実行する。MySQLが起動するまで待機
until mysql -u root -p"${DB_PASSWORD}" -h mysql -e "EXIT"; do
  >&2 echo "MySQL is unavailable - sleeping"
  sleep 5
done

# Laravelのマイグレーションを実行
php artisan migrate --force

# PHP-FPMを起動
exec "$@"
