<?php

namespace Test\Buy;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PagaMasTarde\SeleniumFormUtils\SeleniumHelper;


/**
 * Class BuyRegisteredTest
 * @package Test\Buy
 *
 * @group magento-buy-registered
 */
class BuyRegisteredTest extends AbstractBuy
{
    /**
     * Test Buy Registered
     */
    public function testBuyRegistered()
    {
        $this->prepareProductAndCheckout();
        $this->login();
        $this->fillBillingInformation();
        $this->fillShippingMethod();
        $this->fillPaymentMethod();
        $this->goToPMT(false);
        $this->commitPurchase();
        $this->checkPurchaseReturn(self::CORRECT_PURCHASE_MESSAGE);
        $this->checkLastPurchaseStatus('Processing');
        $this->quit();
    }

    /**
     * Fill the billing information
     */
    public function fillBillingInformation()
    {
        $this->webDriver->executeScript('billing.save()');
        $checkoutStepShippingMethodSearch = WebDriverBy::id('checkout-shipping-method-load');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepShippingMethodSearch);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Login
     */
    public function login()
    {
        $this->findById('login-email')->clear()->sendKeys($this->configuration['email']);
        $this->findById('login-password')->clear()->sendKeys($this->configuration['password']);
        $this->findById('login-form')->submit();

        $billingAddressSelector = WebDriverBy::id('billing-address-select');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($billingAddressSelector);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Commit Purchase
     * @throws \Exception
     */
    public function commitPurchase()
    {
        // complete the purchase with redirect
        SeleniumHelper::finishForm($this->webDriver);
    }
}
