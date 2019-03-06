<?php

namespace Test\Buy;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PagaMasTarde\SeleniumFormUtils\SeleniumHelper;


/**
 * Class BuyUnregisteredTest
 * @package Test\Buy
 *
 * @group magento-cancel-buy-unregistered
 */
class CancelBuyUnregisteredTest extends AbstractBuy
{
    const AMOUNT = '497.54';
    /**
     * Test Buy unregistered
     */
    public function testBuyUnregistered()
    {
        $this->prepareProductAndCheckout();
        $this->selectGuestAndContinue();
        $this->fillBillingInformation();
        $this->fillShippingMethod();
        $this->fillPaymentMethod();
        var_dump($this->webDriver->getTitle());
        $this->goToPMT(false);
        var_dump($this->webDriver->getTitle());
        $this->verifyPaylater();
        var_dump($this->webDriver->getTitle());
        $this->cancelPurchase();
        $this->checkPurchaseReturn(self::SHOPPING_CART_MESSAGE);
        $this->quit();
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

}
