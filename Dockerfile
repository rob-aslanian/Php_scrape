FROM php:7.4-apache
COPY default.conf /etc/apache2/sites-available/default.conf
RUN docker-php-ext-install pdo pdo_mysql mysqli
COPY app /var/www/html/
EXPOSE 80
