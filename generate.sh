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
sleep 130

# Install:

# CLI install (maybe you want to try it also, otherwise it will be installed with backOffice)
docker cp extension/var/connect/DigitalOrigin_Pmt.tgz magento1x_magento-test_1:/tmp/DigitalOrigin_Pmt.tgz
docker exec -it magento1x_magento-test_1 sh -c "ls -ls /tmp"
docker exec -it magento1x_magento-test_1 sh -c "chmod +x mage"
docker exec -it magento1x_magento-test_1 sh -c "./mage channel-add http://connect20.magentocommerce.com/community"
docker exec -it magento1x_magento-test_1 sh -c "./mage install-file /tmp/DigitalOrigin_Pmt.tgz"

# Run test
extension/lib/DigitalOrigin/bin/phpunit --group magento-basic
extension/lib/DigitalOrigin/bin/phpunit --group magento-install
extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-unregistered
