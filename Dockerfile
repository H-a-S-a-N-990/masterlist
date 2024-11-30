# Use the official PHP image from the Docker Hub
FROM php:8.1-apache

# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
