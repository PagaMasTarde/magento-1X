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
        $this->checkCommitedPurchase();
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

    /**
     * Check Commited Purchase
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function checkCommitedPurchase()
    {
        // Check if all goes good
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.page-title h1')
            )
        );
        $successMessage = $this->findByCss('.page-title h1');
        $this->assertContains(
            self::CORRECT_PURCHASE_MESSAGE,
            $successMessage->getText()
        );
    }
}
