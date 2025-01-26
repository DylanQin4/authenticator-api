# Utilise une image PHP avec Apache
FROM php:8.2-apache

# Met à jour les paquets et installe les extensions PHP nécessaires
# netcat est utilisé pour attendre la base de données
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    postgresql-client \
    netcat-openbsd \
    && docker-php-ext-install intl pdo pdo_pgsql

# Activer le module mod_rewrite d'Apache
RUN a2enmod rewrite

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application Symfony
COPY . .

# Configurer les permissions des fichiers
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Configurer Git pour accepter le répertoire de travail
# RUN git config --global --add safe.directory /var/www/html

# Copier le fichier de configuration Apache
COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier le script d'initialisation Symfony
COPY init-symfony.sh /usr/local/bin/init-symfony
RUN chmod +x /usr/local/bin/init-symfony

# Copier le script pour attendre la base de données
COPY wait-for-database.sh /usr/local/bin/wait-for-database
RUN chmod +x /usr/local/bin/wait-for-database

# Exposer le port 80
EXPOSE 80

# Lancer le script d'initialisation et démarrer le serveur
CMD ["init-symfony"]
