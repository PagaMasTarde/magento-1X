<?php

namespace Test\ProductPromotions;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\MagentoTest;

/**
 * Class ProductPageTest
 * @package Test\ProductPromotions
 *
 * @group magento-product-promotions
 */
class ProductPageTest extends MagentoTest
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
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::className('PmtSimulator')
            )
        );

        //TODO check amount matches and presence of copy sentence is present.

        $this->assertTrue((bool) WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::className('PmtSimulator')
        ));

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
