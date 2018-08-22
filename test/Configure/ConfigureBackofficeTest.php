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
        $this->goToPaymentMethodsAndSeePMT();
        $this->configureAndSave();
        $this->webDriver->quit();
    }

    /**
     * Configure and Save
     */
    public function configureAndSave()
    {
        //Fill configuration for PMT
        $this->findById('payment_paylater_active1')->click();
        $this->findById('payment_paylater_PAYLATER_PROD0')->click();
        $this->findById('payment_paylater_PAYLATER_PUBLIC_KEY_TEST')
             ->clear()
             ->sendKeys($this->configuration['publicKey'])
        ;
        $this->findById('payment_paylater_PAYLATER_PRIVATE_KEY_TEST')
             ->clear()
             ->sendKeys($this->configuration['secretKey'])
        ;
        $this->findById('payment_paylater_PAYLATER_PUBLIC_KEY_PROD')
             ->clear()
             ->sendKeys($this->configuration['publicKey'])
        ;
        $this->findById('payment_paylater_PAYLATER_PRIVATE_KEY_PROD')
             ->clear()
             ->sendKeys($this->configuration['secretKey'])
        ;
        $this->findById('payment_paylater_PAYLATER_IFRAME0')->click();
        $this->findById('payment_paylater_PAYLATER_TITLE')->clear()->sendKeys('extra');

        //Confirm and validate
        $this->webDriver->executeScript('configForm.submit()');

        //Verify
        $successMessageSearch = WebDriverBy::className('success-msg');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($successMessageSearch)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::visibilityOfElementLocated($successMessageSearch)
        );
    }
}
