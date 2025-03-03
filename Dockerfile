# ベースイメージとして公式PHPイメージを使用
FROM php:8.3-cli

# 必要なPHP拡張をインストール
RUN docker-php-ext-install pdo pdo_mysql

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Laravelプロジェクトをコンテナにコピー
COPY . /var/www

WORKDIR /var/www

# Composerの依存関係をインストール
RUN composer install --no-dev --optimize-autoloader

# パーミッションの設定（必要に応じて）
RUN chown -R www-data:www-data /var/www

# Laravelアプリケーションのキャッシュを作成（オプション）
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# エントリーポイントスクリプトを設定（オプション）
# ENTRYPOINT ["php", "artisan"]
# ENTRYPOINT ["sh", "-c"]
