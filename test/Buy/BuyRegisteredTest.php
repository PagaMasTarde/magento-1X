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

        // get cart total price
        $cartPrice = WebDriverBy::cssSelector('#checkout-review-table tfoot tr.last .price');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                $cartPrice
            )
        );
        $cartPrice = $this->webDriver->findElement($cartPrice)->getText();
        // --------------------
        $this->goToPMT(false);
        $this->verifyPaylater();
        $this->commitPurchase();
        $this->checkPurchaseReturn(self::CORRECT_PURCHASE_MESSAGE);
        $this->checkLastPurchaseStatus('Processing');

        // get registered purchase amount
        $checkoutPrice = WebDriverBy::cssSelector('.box-account.box-recent .data-table.orders .first .total .price');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                $checkoutPrice
            )
        );
        $checkoutPrice = $this->webDriver->findElement($checkoutPrice)->getText();
        //----------------------

        $this->assertTrue(($cartPrice == $checkoutPrice));
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
     * Verify Paylater
     *
     * @throws \Exception
     */
    public function verifyPaylater()
    {
        $condition = WebDriverExpectedCondition::titleContains(self::PMT_TITLE);
        $this->webDriver->wait(300)->until($condition, $this->webDriver->getCurrentURL());
        $this->assertTrue((bool)$condition, $this->webDriver->getCurrentURL());
    }
}
