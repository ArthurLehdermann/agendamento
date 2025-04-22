FROM arthurlehdermann/alpine-nginx-php8.4

# Instalar dependências do sistema
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    npm

# Instalar extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip exif pcntl gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar configuração do nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Configurar permissões
RUN chown -R nginx:nginx /var/www/html && \
    chmod -R 755 /var/www/html

# Expor porta 80
EXPOSE 80

# Iniciar serviços
CMD ["sh", "-c", "nginx && php-fpm"]
