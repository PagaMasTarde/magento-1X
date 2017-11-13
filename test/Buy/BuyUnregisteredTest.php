<?php

namespace Test\Buy;

/**
 * Class BuyUnregisteredTest
 * @package Test\Buy
 *
 * @group magento-buy-unregistered
 */
class BuyUnregisteredTest extends AbstractBuy
{
    /**
     * Test Buy unregistered
     */
    public function testBuyUnregistered()
    {
        $this->prepareProductAndCheckout();
    }
}
