# Use the official PHP image from the Docker Hub
FROM php:8.1-apache

# Install the necessary packages for SQLite3, SSH, and other extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    openssh-client \
    && docker-php-ext-install pdo pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

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

# Command to start Apache in the foreground and set up serveo
CMD service apache2 start && ssh -R 80:localhost:80 serveo.net
