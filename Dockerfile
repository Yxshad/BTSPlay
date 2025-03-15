# Base PHP avec Apache
FROM php:8.3-apache

# Installer les extensions PHP et outils nécessaires
RUN apt-get update && apt-get install -y \
    cron sudo supervisor nano grep \
    ffmpeg \
    libzip-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    git \
    pure-ftpd \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql mysqli ftp zip curl pcntl 

# Activer le module Apache rewrite
RUN a2enmod rewrite

# Définir un ServerName pour éviter l’avertissement
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Ajouter www-data au sudoers pour exécuter certaines commandes sans mot de passe
RUN echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

# Copier la configuration Apache
COPY ./apachedefaultconf/000-default.conf /etc/apache2/sites-available/000-default.conf

# Activer le site configuré
RUN a2ensite 000-default.conf

# Copier les fichiers PHP
COPY ./racine /var/www/html

# Donner les bons droits et recharger crontab
RUN chmod 0644 /etc/crontab

# 🔥 S'assurer que cron tourne bien
RUN touch /var/log/cron.log

# Donner les droits nécessaires
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposer le port 80    
EXPOSE 80

# Ajouter les tâches cron directement dans /etc/crontab
RUN echo "* * * * * root echo 'CRON TEST' >> /var/log/backup.log 2>&1" >> /etc/crontab

# Lancer cron en arrière-plan avec Apache
CMD cron && tail -f /var/log/cron.log & apache2-foreground
