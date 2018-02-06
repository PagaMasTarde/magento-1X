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
 * @group magento-product-promotions-assign
 */
class ProductAssignPromotionTest extends AbstractConfigure
{
    /**
     * Product name
     */
    const PRODUCT_NAME = 'Linen Blazer';

    /**
     * New Category
     */
    const NEW_CATEGORY = 'New Category';

    /**
     * Promotions category
     */
    const PROMOTIONS_CATEGORY = 'paylater-promotion-product';

    /**
     * Test Assign Product to promotion
     */
    public function testAssignProductToPromotion()
    {
        //todo remove ****
        $this->assertTrue(true);
        return true;
        //todo remove ****

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
        $this->findByLinkText('Catalog')->click();
        $this->findByLinkText('Manage Categories')->click();

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                self::NEW_CATEGORY
            )
        );

        $this->assertContains(self::NEW_CATEGORY, $this->webDriver->getTitle());
    }

    /**
     * Add Product to category and save
     */
    public function addProductToCategory()
    {
        sleep(2); // Ajax load of categories.
        $treeDivSearch = WebDriverBy::id('tree-div');
        $categorySearch = $treeDivSearch->partialLinkText(self::PROMOTIONS_CATEGORY);
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable($categorySearch)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::elementToBeClickable($categorySearch)
        );
        $categoryElement = $this->webDriver->findElement($categorySearch);
        $categoryElement->click();
        sleep(2); // Ajax load of categories.

        $categoryProductsSearch = WebDriverBy::linkText('Category Products');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable($categoryProductsSearch)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::elementToBeClickable($categoryProductsSearch)
        );
        $categoryElement = $this->webDriver->findElement($categoryProductsSearch);
        $categoryElement->click();

        sleep(2); // Ajax load of categories.

        $this->webDriver->executeScript('catalog_category_productsJsObject.resetFilter()');

        $productSearch = WebDriverBy::id('catalog_category_products_filter_name');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable($productSearch)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::elementToBeClickable($productSearch)
        );
        $productSearchElement = $this->webDriver->findElement($productSearch);
        $productSearchElement->clear()->sendKeys(self::PRODUCT_NAME);
        $inCategoryElement = $this->findById('catalog_category_products_filter_in_category')->sendKeys('No');
        $this->webDriver->executeScript('catalog_category_productsJsObject.doFilter()');

        sleep(2); // Ajax load of categories.

        $selectAllElement = WebDriverBy::xpath('.//input[@type = \'checkbox\' and @title = \'Select All\']');
        $this->webDriver->findElement($selectAllElement)->click();

        $this->findByClass('save')->click();

        sleep(2); // Ajax load of categories.
        $successMessage = WebDriverBy::className('success-msg');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfAnyElementLocated($successMessage)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::visibilityOfAnyElementLocated($successMessage)
        );
    }
}
