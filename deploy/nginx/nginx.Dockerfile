FROM --platform=linux/amd64 nginx:latest

# nginx設定ファイルをコピー
COPY ./deploy/nginx/default.conf /etc/nginx/conf.d/default.conf

# ログディレクトリを作成
RUN mkdir -p /var/www/html/docker/nginx/logs

# アプリケーションの静的ファイルをコピー
COPY ./public /var/www/html/public

# noimage.pngをコピー
COPY ./public/noimage.png /var/www/html/public/noimage.png

# Entrypointスクリプトをコピー
COPY ./deploy/nginx/entrypoint.sh /usr/local/bin/entrypoint.sh

# Entrypointスクリプトに実行権限を付与
RUN chmod +x /usr/local/bin/entrypoint.sh

# ポートを開放
EXPOSE 80
EXPOSE 443

# Entrypointスクリプトを使用
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
