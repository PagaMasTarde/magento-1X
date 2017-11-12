<?php

namespace Test\ProductPage;

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
     * Simulator sentence
     */
    const SIMULATOR_SENTENCE = 'Simula aquí tu financiación';

    /**
     * testSimulatorDivExists
     */
    public function testSimulgatorDivExists()
    {
        $this->gotToProductPage();
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementTextIs(
                WebDriverBy::className('PmtSimulator-textClaim'),
                self::SIMULATOR_SENTENCE
            )
        );
        $this->assertTrue((bool) WebDriverExpectedCondition::elementTextIs(
            WebDriverBy::className('PmtSimulator-textClaim'),
            self::SIMULATOR_SENTENCE
        ));

        $this->quit();
    }

    /**
     * Go to the product page
     */
    public function gotToProductPage()
    {
        $this->webDriver->get(self::MAGENTO_URL);
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::linkText(strtoupper(self::PRODUCT_NAME))
            )
        );
        $this->findByLinkText(strtoupper(self::PRODUCT_NAME))->click();
        $this->assertSame(
            self::PRODUCT_NAME,
            $this->webDriver->getTitle()
        );
    }
}
