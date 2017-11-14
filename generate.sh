#!/bin/bash

# Prepare environment
set -e
docker-compose down
docker-compose up -d
sleep 60
composer install
npm install
grunt

# Build Package todo

# Run test
extension/lib/DigitalOrigin/bin/phpunit --group magento-basic
extension/lib/DigitalOrigin/bin/phpunit --group magento-install
extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-unregistered
