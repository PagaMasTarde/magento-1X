#!/bin/bash

# Prepare environment and build package
docker-compose pull
docker-compose down
docker-compose up -d --build magento-test db-test selenium
composer install

# Time to boot and install magento
sleep 30
set -e

# Run test
extension/lib/DigitalOrigin/bin/phpunit --group magento-basic
extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice-iframe
extension/lib/DigitalOrigin/bin/phpunit --group magento-product-page
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-unregistered
extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice-redirect
extension/lib/DigitalOrigin/bin/phpunit --group magento-register
extension/lib/DigitalOrigin/bin/phpunit --group magento-fill-data
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-registered
