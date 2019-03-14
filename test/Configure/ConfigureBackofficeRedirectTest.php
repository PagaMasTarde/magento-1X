<?php

namespace Test\Configure;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Class ConfigureBackofficeTest
 * @package Test\Configure
 *
 * @group magento-configure-backoffice-redirect
 */
class ConfigureBackofficeRedirectTest extends AbstractConfigure
{
    /**
     * testConfigureBackoffice
     */
    public function testConfigureBackoffice()
    {
        $this->getBackofficeLoggedIn();
        $this->goToSystemConfig();
        $this->goToPaymentMethodsAndSeePagantis();
        $this->configureAndSave();
        $this->webDriver->quit();
    }
}
