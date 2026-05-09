FROM php:8.2-apache

# instala extensão mysqli
RUN docker-php-ext-install mysqli

# (opcional, mas recomendado)
RUN docker-php-ext-enable mysqli

# ativa mod_rewrite (caso use rotas futuramente)
RUN a2enmod rewrite

RUN docker-php-ext-install mysqli \
    && mkdir -p /var/lib/php/sessions \
    && chown -R www-data:www-data /var/lib/php/sessions

ENV variables_order=EGPCS