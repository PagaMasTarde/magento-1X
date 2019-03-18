<?php

namespace Test\Configure;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Class ConfigureBackofficeTest
 * @package Test\Configure
 *
 * @group magento-configure-backoffice
 */
class ConfigureBackofficeTest extends AbstractConfigure
{
    /**
     * testConfigureBackoffice
     */
    public function testConfigureBackoffice()
    {
        $this->getBackofficeLoggedIn();
        $this->goToSystemConfig();
        $this->goToShippingMethodsAndSeeFedEx();
        $this->disableFedEx();
        $this->goToSystemConfig();
        $this->goToPaymentMethodsAndSeePagantis();
        $this->configureAndSave();
        $this->webDriver->quit();
    }
}
