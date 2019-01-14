#!/bin/bash

# Prepare environment and build package
docker-compose down
docker-compose up -d --build magento-test db-test selenium
docker-compose exec magento-test install-sampledata
docker-compose exec magento-test install-magento
docker-compose exec magento-test modman init
docker-compose exec magento-test modman link /pmt
docker-compose exec magento-test modman deploy

set -e
# Run test
composer install

../extension/lib/DigitalOrigin/bin/phpunit --group magento-basic
../extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice-iframe
../extension/lib/DigitalOrigin/bin/phpunit --group magento-product-page
../extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-unregistered
../extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice-redirect
../extension/lib/DigitalOrigin/bin/phpunit --group magento-cancel-buy-unregistered
../extension/lib/DigitalOrigin/bin/phpunit --group magento-register
../extension/lib/DigitalOrigin/bin/phpunit --group magento-fill-data
../extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-registered
../extension/lib/DigitalOrigin/bin/phpunit --group magento-cancel-buy-registered

composer install --no-dev