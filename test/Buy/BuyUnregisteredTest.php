<?php

namespace Test\Buy;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Class BuyUnregisteredTest
 * @package Test\Buy
 *
 * @group magento-buy-unregistered
 */
class BuyUnregisteredTest extends AbstractBuy
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
        $this->goToPMT(false);
        $this->quit();
    }
}
