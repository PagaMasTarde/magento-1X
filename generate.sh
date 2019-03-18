#!/bin/bash

# Prepare environment and build package
docker-compose down
docker-compose up -d --build magento-test
if [ $1 == 'true' ]
then
    docker-compose up -d --build selenium
fi
sleep 10

# Copy Files to magento test
docker cp ./extension/. magento19test:/pagantis/

# Install magento and sample data
docker-compose exec magento-test install-sampledata
docker-compose exec magento-test install-magento

# Install modman and enable the link creation
docker-compose exec magento-test modman init
docker-compose exec magento-test modman link /pagantis

# Install n98-magerun to enable automatically dev:symlinks so that modman works
docker-compose exec magento-test curl -O https://files.magerun.net/n98-magerun.phar
docker-compose exec magento-test chmod +x n98-magerun.phar
docker-compose exec magento-test ./n98-magerun.phar dev:symlinks 1

set -e
# Run test
composer install

if [ $1 == 'true' ]
then
    echo magento-basic
    extension/lib/Pagantis/bin/phpunit --group magento-basic
    echo magento-configure-backoffice-iframe
    extension/lib/Pagantis/bin/phpunit --group magento-configure-backoffice
    echo magento-product-page
    extension/lib/Pagantis/bin/phpunit --group magento-product-page
    echo magento-buy-unregistered
    extension/lib/Pagantis/bin/phpunit --group magento-buy-unregistered
    echo magento-cancel-buy-unregistered
    extension/lib/Pagantis/bin/phpunit --group magento-cancel-buy-unregistered
    echo magento-register
    extension/lib/Pagantis/bin/phpunit --group magento-register
    echo magento-fill-data
    extension/lib/Pagantis/bin/phpunit --group magento-fill-data
    echo magento-buy-registered
    extension/lib/Pagantis/bin/phpunit --group magento-buy-registered
    echo magento-cancel-buy-registered
    extension/lib/Pagantis/bin/phpunit --group magento-cancel-buy-registered
    echo magento-cancel-buy-controllers
    extension/lib/Pagantis/bin/phpunit --group magento-cancel-buy-controllers
else
    echo magento-configure-backoffice-redirect
    extension/lib/Pagantis/bin/phpunit --group magento-configure-backoffice
fi