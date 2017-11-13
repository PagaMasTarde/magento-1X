<?php

namespace Test\Buy;

/**
 * Class BuyRegisteredTest
 * @package Test\Buy
 *
 * @group magento-buy-registered
 */
class BuyRegisteredTest extends AbstractBuy
{
    /**
     * Test Buy unregistered
     */
    public function testBuyUnregistered()
    {
        $this->prepareProductAndCheckout();
    }
}
