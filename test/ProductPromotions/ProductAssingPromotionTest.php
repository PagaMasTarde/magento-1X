<?php

namespace Test\ProductPromotions;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Configure\AbstractConfigure;
use Test\MagentoTest;

/**
 * Class ProductAssingPromotionTest
 * @package Test\ProductPromotions
 *
 * @group magento-product-promotions
 */
class ProductAssingPromotionTest extends AbstractConfigure
{
    /**
     * Product name
     */
    const PRODUCT_NAME = 'Linen Blazer';

    /**
     * Test Assign Product to promotion
     */
    public function testAssignProductToPromotion()
    {
        $this->getBackofficeLoggedIn();
        $this->gotToCategories();
        $this->addProductToCategory();
        $this->webDriver->quit();
    }

    /**
     * Go to category and check paylater-promotion-product exists
     */
    public function gotToCategories()
    {
        //todo
    }

    /**
     * Add Product to category and save
     */
    public function addProductToCategory()
    {
        //todo
    }
}
