#!/bin/bash

# Prepare environment
set -e
docker-compose down
docker-compose up -d
composer install
npm install

# Build Package todo
grunt
ls -lshc extension/var/connect/

# Time to boot and install magento
sleep 150

# Run test
extension/lib/DigitalOrigin/bin/phpunit --group magento-basic
extension/lib/DigitalOrigin/bin/phpunit --group magento-install
extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-unregistered
