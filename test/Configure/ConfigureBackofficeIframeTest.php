<?php

namespace Test\Configure;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Class ConfigureBackofficeTest
 * @package Test\Configure
 *
 * @group magento-configure-backoffice-iframe
 */
class ConfigureBackofficeIframeTest extends AbstractConfigure
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
        $this->goToPaymentMethodsAndSeePMT();
        $this->configureAndSave('payment_paylater_PAYLATER_IFRAME1');
        $this->webDriver->quit();
    }
}
