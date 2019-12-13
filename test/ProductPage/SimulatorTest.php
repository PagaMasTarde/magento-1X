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
     * Product name in magento 16
     */
    const PRODUCT_NAME_16 = 'Olympus Stylus 750 7.1MP Digital Camera';

    /**
     * Product name in magento 19
     */
    const PRODUCT_NAME_19 = 'Linen Blazer';

    /**
     * testSimulatorDivExists
     */
    public function testSimulatorDivExists()
    {
        $this->goToProductPage();

        $pagantisSimulator = WebDriverBy::className('PagantisSimulator');

        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                $pagantisSimulator
            )
        );
        $this->assertTrue((bool) WebDriverExpectedCondition::presenceOfElementLocated(
            $pagantisSimulator
        ));

        $this->quit();
    }

    /**
     * Go to the product page
     */
    public function goToProductPage()
    {
        $this->webDriver->get($this->magentoUrl);

        $productName = $this->version = "16" ? self::PRODUCT_NAME_16 : self::PRODUCT_NAME_19;
        /** @var WebDriverBy $pattialProductLink */
        $productLinkSearch = WebDriverBy::partialLinkText($productName);

        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                $productLinkSearch
            )
        );

        $productLinkElement = $this->webDriver->findElement($productLinkSearch);
        $this->webDriver->executeScript("arguments[0].scrollIntoView(true);", array($productLinkElement));
        $productLinkElement->click();

        $this->assertContains(
            $productName,
            $this->webDriver->getTitle()
        );
    }
}
