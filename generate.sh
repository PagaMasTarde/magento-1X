#!/bin/bash

# Prepare environment and build package
if [ $2 == 'build' ]
then
    docker-compose down
fi

docker-compose up -d --build magento-$1
if [ $1 == 'test' ]
then
    docker-compose up -d --build selenium
fi
sleep 10

# Install magento and sample data
docker-compose exec -u www-data magento-$1 install-sampledata
docker-compose exec -u www-data magento-$1 install-magento

# Install modman and enable the link creation
if [ $1 == 'test' ]
then
    docker-compose exec -u www-data magento-$1 modman init
    docker-compose exec -u www-data magento-$1 modman link /pmt
fi

# Install n98-magerun to enable automatically dev:symlinks so that modman works
docker-compose exec -u www-data magento-$1 curl -O https://files.magerun.net/n98-magerun.phar
docker-compose exec -u www-data magento-$1 chmod +x n98-magerun.phar
docker-compose exec -u www-data magento-$1 ./n98-magerun.phar dev:symlinks 1

set -e
# Run test
composer install

if [ $1 == 'test' ]
then
    extension/lib/DigitalOrigin/bin/phpunit --group magento-basic
    extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice-iframe
    extension/lib/DigitalOrigin/bin/phpunit --group magento-product-page
    extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-unregistered
    extension/lib/DigitalOrigin/bin/phpunit --group magento-cancel-buy-unregistered
    extension/lib/DigitalOrigin/bin/phpunit --group magento-register
    extension/lib/DigitalOrigin/bin/phpunit --group magento-fill-data
    extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-registered
    extension/lib/DigitalOrigin/bin/phpunit --group magento-cancel-buy-registered
fi