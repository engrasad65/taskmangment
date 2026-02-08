FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_sqlite \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

RUN mkdir -p /var/www/html/storage /var/www/html/public/uploads/start /var/www/html/public/uploads/end \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80
CMD ["apache2-foreground"]
