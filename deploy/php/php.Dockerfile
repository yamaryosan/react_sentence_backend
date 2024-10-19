FROM --platform=linux/amd64 php:8.2-fpm

RUN apt-get update \
    && apt-get install -y \
    git \
    zip \
    unzip \
    vim \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && apt-get clean

# php.iniをコピー
COPY ./deploy/php/php.ini /usr/local/etc/php/php.ini
# 現在のディレクトリのファイルを/var/www/htmlにコピー
COPY ../../app /var/www/html/app
COPY ../../bootstrap /var/www/html/bootstrap
COPY ../../config /var/www/html/config
COPY ../../database /var/www/html/database
COPY ../../public /var/www/html/public
COPY ../../resources /var/www/html/resources
COPY ../../routes /var/www/html/routes
COPY ../../storage /var/www/html/storage
COPY ../../artisan /var/www/html/artisan
COPY ../../composer.json /var/www/html/composer.json
COPY ../../composer.lock /var/www/html/composer.lock
COPY ../../.env /var/www/html/.env

# PHP拡張をインストール(Laravelで必要)
RUN docker-php-ext-install pdo_mysql mbstring

# Composerをインストール(Laravel10では2.2.0以上)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www/html
# 依存関係をインストール
RUN composer install --no-interaction --optimize-autoloader --no-dev
# シンボリックリンクを作成
RUN php artisan storage:link
# ストレージディレクトリとpublicディレクトリのパーミッションを変更(静的コンテンツアップロード用)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/storage/app/public /var/www/html/public
RUN chmod -R 755 /var/www/html/storage /var/www/html/storage/app/public /var/www/html/public
# アプリケーションキーを生成
RUN php artisan key:generate --force

# エントリーポイントスクリプトをコンテナ内にコピー
COPY ./deploy/php/entrypoint.sh /usr/local/bin/entrypoint.sh
# エントリーポイントスクリプトに実行権限を付与
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]

