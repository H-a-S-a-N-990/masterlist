# Use the official PHP image with Apache
FROM php:8.0-apache

# Install SQLite3 and enable the extension
RUN docker-php-ext-install pdo pdo_sqlite

# Set the working directory
WORKDIR /var/www/html

# Copy the application files to the container
COPY . .

# Create the database
RUN php init_db.php

# Set permissions (if necessary)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
