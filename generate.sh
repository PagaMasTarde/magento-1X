#!/bin/bash

# Prepare environment and build package
docker-compose down
docker-compose up -d
composer install
npm install
grunt

# Time to boot and install magento
sleep 30
set -e

docker exec -it magento1x_magento-test_1 sh -c "chmod -R 777 /var/www"
docker exec -it magento1x_magento-test_1 sh -c "chmod -R 777 /pmt"
docker exec -it magento1x_magento-test_1 sh -c "ls -lshc /pmt/var/connect/"
# CLI install (maybe you want to try it also, otherwise it will be installed with backOffice)
docker exec -it magento1x_magento-test_1 sh -c "chmod +x mage"
docker exec -it magento1x_magento-test_1 sh -c "./mage channel-add http://connect20.magentocommerce.com/community"
docker exec -it magento1x_magento-test_1 sh -c "./mage install-file /pmt/var/connect/DigitalOrigin_Pmt.tgz"

# Run test
extension/lib/DigitalOrigin/bin/phpunit --group magento-basic
extension/lib/DigitalOrigin/bin/phpunit --group magento-install
extension/lib/DigitalOrigin/bin/phpunit --group magento-configure-backoffice
extension/lib/DigitalOrigin/bin/phpunit --group magento-buy-unregistered
