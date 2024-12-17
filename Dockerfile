# Utilise une image PHP avec Apache
FROM php:8.2-apache

# Met à jour les paquets et installe les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
#    git \
#    unzip \
#    php-zip \
    libicu-dev \
    libpq-dev \
    libonig-dev \
    && docker-php-ext-install intl pdo pdo_pgsql

# Activer le module mod_rewrite d'Apache
RUN a2enmod rewrite

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application Symfony
COPY . .

# Configurer Git pour accepter le répertoire de travail
# RUN git config --global --add safe.directory /var/www/html

# Copier le fichier de configuration Apache
COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Exposer le port 80
EXPOSE 80

# Lancer le serveur Apache
CMD ["apache2-foreground"]