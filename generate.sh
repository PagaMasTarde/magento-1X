#!/bin/bash

# Prepare environment and build package
docker-compose down
docker-compose up -d
composer install

# Time to boot and install magento
sleep 20
set -e

# Run test
extension/lib/DigitalOrigin/bin/phpunit --group magento-basic
extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice
extension/lib/DigitalOrigin/bin/phpunit --group magento-product-page
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-unregistered
extension/lib/DigitalOrigin/bin/phpunit --group magento-register
extension/lib/DigitalOrigin/bin/phpunit --group magento-fill-data
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-registered
