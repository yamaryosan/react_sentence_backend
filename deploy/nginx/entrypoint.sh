#!/bin/sh

# noimage.pngをコピー
cp /var/www/html/public/noimage.png /var/www/html/public/storage/noimage.png

# Nginxを起動
nginx -g 'daemon off;'
