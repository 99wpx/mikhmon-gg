FROM alpine:3.19

LABEL maintainer="99wpx"

# Install nginx, PHP 8.1, supervisor, etc.
RUN apk update && apk add --no-cache \
    nginx \
    php81 \
    php81-fpm \
    php81-gd \
    php81-mysqli \
    php81-mbstring \
    php81-session \
    supervisor \
    curl \
    bash \
    unzip \
    tzdata \
    && ln -s /usr/bin/php81 /usr/bin/php \
    && mkdir -p /run/nginx

# Copy source code
COPY ./mikhmon /var/www/localhost/htdocs
COPY supervisord.conf /etc/supervisord.conf
COPY nginx.conf /etc/nginx/nginx.conf

# Fix permissions
RUN chown -R nginx:nginx /var/www/localhost/htdocs

# Expose HTTP port
EXPOSE 80

# Start all services
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
