<?php

namespace Test\ProductPage;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\MagentoTest;

/**
 * Class SimulatorTest
 * @package Test\ProductPage
 *
 * @group magento-product-page
 */
class SimulatorTest extends MagentoTest
{
    /**
     * Product name
     */
    const PRODUCT_NAME = 'Linen Blazer';

    /**
     * testSimulatorDivExists
     */
    public function testSimulatorDivExists()
    {
        $this->goToProductPage();

        $pmtSimulator = WebDriverBy::className('PmtSimulator');

        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                $pmtSimulator
            )
        );
        $this->assertTrue((bool) WebDriverExpectedCondition::presenceOfElementLocated(
            $pmtSimulator
        ));

        /*** TODO UNCOMMENT THIS WHEN PPP
        $promotionMessage = $this->findByClass('pmt-promotion');

        $this->assertTrue(
            (bool) WebDriverExpectedCondition::visibilityOf($promotionMessage)
        );

        $discount = $this->webDriver->findElement($pmtSimulator)->getAttribute('data-pmt-discount');
        $this->assertSame('1', $discount);
        ***/

        $this->quit();
    }

    /**
     * Go to the product page
     */
    public function goToProductPage()
    {
        $this->webDriver->get(self::MAGENTO_URL);

        /** @var WebDriverBy $productGrid */
        $productGridSearch = WebDriverBy::className('products-grid');
        /** @var WebDriverBy $productLink */
        $productLinkSearch = $productGridSearch->linkText(strtoupper(self::PRODUCT_NAME));

        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                $productLinkSearch
            )
        );

        $productLinkElement = $this->webDriver->findElement($productLinkSearch);
        $this->webDriver->executeScript("arguments[0].scrollIntoView(true);", array($productLinkElement));
        $productLinkElement->click();

        $this->assertSame(
            self::PRODUCT_NAME,
            $this->webDriver->getTitle()
        );
    }
}
