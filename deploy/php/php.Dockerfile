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
    libpq-dev \
    postgresql-client \
    && apt-get clean

# php.iniをコピー
COPY ./deploy/php/php.ini /usr/local/etc/php/php.ini
# 現在のディレクトリのファイルを/var/www/htmlにコピー
COPY . /var/www/html

# PHP拡張をインストール(Laravelで必要)
RUN docker-php-ext-install pdo_mysql mbstring pdo_pgsql

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

# エントリーポイントスクリプトをコピー
COPY ./deploy/php/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80", "--env=.env"]
