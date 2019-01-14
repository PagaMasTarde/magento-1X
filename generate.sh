#!/bin/bash

# Prepare environment and build package
docker-compose down
docker-compose up -d --build magento-test
sleep 10

# Install magento and sample data
docker-compose exec magento-test install-sampledata
docker-compose exec magento-test install-magento

# Install modman and enable the link creation
docker-compose exec magento-test modman init
docker-compose exec magento-test modman link /pmt

# Install n98-magerun to enable automatically dev:symlinks so that modman works
docker-compose exec magento-test curl -O https://files.magerun.net/n98-magerun.phar
docker-compose exec magento-test chmod +x n98-magerun.phar
docker-compose exec magento-test ./n98-magerun.phar dev:symlinks 1

set -e
# Run test
composer install
extension/lib/DigitalOrigin/bin/phpunit --group magento-basic
extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice-iframe
extension/lib/DigitalOrigin/bin/phpunit --group magento-product-page
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-unregistered
extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice-redirect
extension/lib/DigitalOrigin/bin/phpunit --group magento-cancel-buy-unregistered
extension/lib/DigitalOrigin/bin/phpunit --group magento-register
extension/lib/DigitalOrigin/bin/phpunit --group magento-fill-data
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-registered
extension/lib/DigitalOrigin/bin/phpunit --group magento-cancel-buy-registered
composer install --no-dev
