<?php

namespace Test\Buy;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PagaMasTarde\SeleniumFormUtils\SeleniumHelper;

/**
 * Class CancelBuyRegisteredTest
 * @package Test\Buy
 *
 * @group magento-cancel-buy-registered
 */
class CancelBuyRegisteredTest extends AbstractBuy
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
        $this->cancelPurchase();
        $this->checkCanceledPurchase();
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
     * Cancel Purchase
     * @throws \Exception
     */
    public function cancelPurchase()
    {
        // complete the purchase with redirect
        SeleniumHelper::cancelForm($this->webDriver);
    }

    /**
     * Check Canceled Purchase
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function checkCanceledPurchase()
    {
        // Check if all goes good
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.page-title h1')
            )
        );
        $successMessage = $this->findByCss('.page-title h1');
        $this->assertContains(
            self::SHOPPING_CART_MESSAGE,
            $successMessage->getText()
        );
    }
}