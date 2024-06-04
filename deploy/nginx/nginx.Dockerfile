FROM --platform=linux/amd64 nginx:latest

# nginx設定ファイルをコピー
COPY ./deploy/nginx/default.conf /etc/nginx/conf.d/default.conf

# ログディレクトリを作成
RUN mkdir -p /var/www/html/docker/nginx/logs

# アプリケーションの静的ファイルをコピー
COPY ./public /var/www/html/public

# ポートを開放
EXPOSE 80
EXPOSE 443

# コンテナ起動時にnginxを起動
CMD ["nginx", "-g", "daemon off;"]
