# Use the official PHP image from the Docker Hub
FROM php:8.1-apache

# Install SQLite3 and enable the extension
RUN docker-php-ext-install pdo pdo_sqlite

# Set the working directory
WORKDIR /var/www/html

# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html/

# Set the ServerName to suppress the warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Enable mod_rewrite (if necessary)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80
