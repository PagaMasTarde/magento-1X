<?php

namespace Test\Configure;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\MagentoTest;

/**
 * Class AbstractConfigure
 * @package Test\Configure
 */
abstract class AbstractConfigure extends MagentoTest
{
    /**
     * Backoffice Title
     */
    const BACKOFFICE_TITLE = 'Log into Magento Admin Page';

    /**
     * Logged in
     */
    const BACKOFFICE_LOGGED_IN_TITLE = 'Dashboard / Magento Admin';

    /**
     * Configuration System
     */
    const BACKOFFICE_CONFIGURATION_TITLE = 'Configuration / System';

    /**
     * getBackOffice
     */
    public function getBackOffice()
    {
        $this->webDriver->get(self::MAGENTO_URL.self::BACKOFFICE_FOLDER);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                self::BACKOFFICE_TITLE
            )
        );

        $this->assertContains(self::BACKOFFICE_TITLE, $this->webDriver->getTitle());
    }

    /**
     * loginToBackoffice
     */
    public function loginToBackoffice()
    {
        //Fill the username and password
        $this->findById('username')->sendKeys($this->configuration['backofficeUsername']);
        $this->findById('login')->sendKeys($this->configuration['backofficePassword']);

        //Submit form:
        $form = $this->findById('loginForm');
        $form->submit();

        //Verify
        $this->webDriver->executeScript('closeMessagePopup()');
        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                self::BACKOFFICE_LOGGED_IN_TITLE
            )
        );

        $this->assertContains(self::BACKOFFICE_LOGGED_IN_TITLE, $this->webDriver->getTitle());
    }

    /**
     * getBackofficeLoggedIn
     */
    public function getBackofficeLoggedIn()
    {
        $this->getBackOffice();
        $this->loginToBackoffice();
    }

    /**
     * goToSystemConfig
     */
    public function goToSystemConfig()
    {
        $this->findByLinkText('System')->click();
        $this->findByLinkText('Configuration')->click();

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                self::BACKOFFICE_CONFIGURATION_TITLE
            )
        );

        $this->assertContains(self::BACKOFFICE_CONFIGURATION_TITLE, $this->webDriver->getTitle());
    }

    /**
     * goToSystemConfig
     */
    public function getToModuleAdd()
    {
        $this->findByLinkText('System')->click();
        $this->findByLinkText('Magento Connect')->click();
        $this->findByLinkText('Magento Connect Manager')->click();

        try {
            $this->findById('username')->clear()->sendKeys($this->configuration['backofficeUsername']);
            $this->findById('password')->clear()->sendKeys($this->configuration['backofficePassword']);
            $this->findByName('form_key')->submit();
        } catch (\Exception $exception) {
            echo 'already magento connect';
        }

        $fileFormSearch = WebDriverBy::id('file');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                $fileFormSearch
            )
        );

        $this->assertTrue((bool) WebDriverExpectedCondition::visibilityOfElementLocated(
            $fileFormSearch
        ));
    }


    /**
     * goToSystemConfig
     */
    public function goToPaymentMethodsAndSeePMT()
    {
        $paymentMethodsLinkElement = $this->findByLinkText('Payment Methods');
        $this->webDriver->executeScript("arguments[0].scrollIntoView(true);", array($paymentMethodsLinkElement));
        $paymentMethodsLinkElement->click();

        $pmtHeaderSearch = WebDriverBy::id('payment_paylater-head');
        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                $pmtHeaderSearch
            )
        );

        $this->assertTrue((bool) WebDriverExpectedCondition::visibilityOfElementLocated(
            $pmtHeaderSearch
        ));
    }
}
