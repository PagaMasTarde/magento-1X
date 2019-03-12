<?php

namespace Test\Configure;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;
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
     * goToPaymentMethodsAndSeePMT
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

    /**
     * goToShippingMethodsAndSeeFedEx
     */
    public function goToShippingMethodsAndSeeFedEx()
    {
        $shippingMethodsLinkElement = $this->findByLinkText('Shipping Methods');
        $shippingMethodsLinkElement->click();

        $fedExHeaderSearch = WebDriverBy::id('carriers_fedex-head');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                $fedExHeaderSearch
            )
        );

        $this->assertTrue((bool) WebDriverExpectedCondition::visibilityOfElementLocated(
            $fedExHeaderSearch
        ));
        $head = $this->findById('carriers_fedex-head');
        $head->click();
    }

    /**
     * disableFedEx
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    public function disableFedEx() {
        $select = new WebDriverSelect($this->findById('carriers_fedex_active'));
        $select->selectByValue('0');

        //Confirm and validate
        $this->webDriver->executeScript('configForm.submit()');
    }

    /**
     * Configure and Save
     */
    public function configureAndSave()
    {
        //Fill configuration for PMT
        $this->findById('payment_paylater_active1')->click();
        $this->findById('payment_paylater_pmt_public_key')
            ->clear()
            ->sendKeys($this->configuration['publicKey'])
        ;
        $this->findById('payment_paylater_pmt_private_key')
            ->clear()
            ->sendKeys($this->configuration['secretKey'])
        ;        //Confirm and validate
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
