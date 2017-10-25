#!/bin/bash

cd /var/www/html/

if [ ! -f app/etc/local.xml ]; then

    RET=1
    while [ $RET -ne 0 ]; do
        mysql -h $MAGENTO_DB_HOST -u $MAGENTO_DB_USER -p$MAGENTO_DB_PASSWORD -e "status" > /dev/null 2>&1
        RET=$?
        if [ $RET -ne 0 ]; then
            echo "\n* Waiting for confirmation of MySQL service startup";
            sleep 5
        fi
    done

    echo "Install Magento sample data"
    mysql -h $MAGENTO_DB_HOST -u $MAGENTO_DB_USER -p$MAGENTO_DB_PASSWORD $MAGENTO_DATABASE < /tmp/magento-sample-data-1.9.1.0/magento_sample_data_for_1.9.1.0.sql

    echo "Install Magento"
    php -f install.php -- --license_agreement_accepted "yes" \
        --locale "$MAGENTO_LOCALE" \
        --timezone "$MAGENTO_TIMEZONE" \
        --default_currency $MAGENTO_DEFAULT_CURRENCY \
        --db_host $MAGENTO_DB_HOST \
        --db_name $MAGENTO_DATABASE \
        --db_user $MAGENTO_DB_USER \
        --db_pass "$MAGENTO_DB_PASSWORD" \
        --url "$MAGENTO_URL" \
        --skip_url_validation "yes" \
        --use_rewrites "yes" \
        --use_secure "no" \
        --secure_base_url "" \
        --use_secure_admin "no" \
        --admin_firstname "$MAGENTO_ADMIN_FIRSTNAME" \
        --admin_lastname "$MAGENTO_ADMIN_LASTNAME" \
        --admin_email "$MAGENTO_ADMIN_EMAIL" \
        --admin_username "$MAGENTO_ADMIN_USERNAME" \
        --admin_password "$MAGENTO_ADMIN_PASSWORD" \
        || true

    echo "Magento installed"

    php n98-magerun.phar --skip-root-check cache:disable
    php n98-magerun.phar --skip-root-check config:set 'web/seo/use_rewrites' '0'
    php n98-magerun.phar --skip-root-check config:set 'dev/template/allow_symlink' '1'
    php n98-magerun.phar --skip-root-check config:set 'general/country/default' 'ES'

    echo "Magento configured"
fi

exec "$@"