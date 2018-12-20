<?php

namespace Test\Buy;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\MagentoTest;

/**
 * Class AbstractBuy
 * @package Test\Buy
 */
abstract class AbstractBuy extends MagentoTest
{
    /**
     * Color of jacket
     */
    const COLOR = 'White';

    /**
     * Size of jacket
     */
    const SIZE = 'S';

    /**
     * Grand Total
     */
    const GRAND_TOTAL = 'GRAND TOTAL';

    /**
     * Product name
     */
    const PRODUCT_NAME = 'Linen Blazer';

    /**
     * Correct purchase message
     */
    const CORRECT_PURCHASE_MESSAGE = 'YOUR ORDER HAS BEEN RECEIVED.';

    /**
     * Canceled purchase message
     */
    const CANCELED_PURCHASE_MESSAGE = 'YOUR ORDER HAS BEEN CANCELED.';

   /**
     * Shopping cart message
     */
    const SHOPPING_CART_MESSAGE = 'SHOPPING CART';

    /**
     * Empty shopping cart message
     */
    const EMPTY_SHOPPING_CART = 'SHOPPING CART IS EMPTY';

    /**
     * Pmt Order Title
     */
    const PMT_TITLE = 'Paga+Tarde';

    /**
     * Notification route
     */
    const NOTIFICATION_FOLDER = '/pmt/notify';

    /**
     * Buy unregistered
     */
    public function prepareProductAndCheckout()
    {
        $this->goToProductPage();
        $this->selectColorAndSize();
        $this->addToCart();
    }

    /**
     * testAddToCart
     */
    public function addToCart()
    {
        $addToCartButtonSearch = WebDriverBy::cssSelector('.add-to-cart-buttons button');
        $addToCartButtonElement = $this->webDriver->findElement($addToCartButtonSearch);
        $this->webDriver->executeScript("arguments[0].scrollIntoView(true);", array($addToCartButtonElement));
        $addToCartButtonElement->click();
        $cartTotalsSearch = WebDriverBy::className('cart-totals');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementTextContains(
                $cartTotalsSearch,
                self::GRAND_TOTAL
            )
        );
        $checkoutButtonSearch = $cartTotalsSearch->className('btn-proceed-checkout');
        $checkoutButtonElement = $this->webDriver->findElement($checkoutButtonSearch);
        $checkoutButtonElement->click();
        $checkoutStepLoginSearch = WebDriverBy::id('checkout-step-login');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                $checkoutStepLoginSearch
            )
        );
        $this->assertTrue((bool) WebDriverExpectedCondition::visibilityOfElementLocated(
            $checkoutStepLoginSearch
        ));
    }

    /**
     * selectColorAndSize
     */
    public function selectColorAndSize()
    {
        $colorWhiteSearch = WebDriverBy::className('option-white');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                $colorWhiteSearch
            )
        );
        $colorWhiteElement = $this->webDriver->findElement($colorWhiteSearch);
        $colorWhiteElement->click();

        $optionSmallSearch = WebDriverBy::className('option-s');

        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                $optionSmallSearch
            )
        );
        $optionSmallElement = $this->webDriver->findElement($optionSmallSearch);
        $optionSmallElement->click();
        $colorSelectorLabelSearch = WebDriverBy::id('select_label_color');
        $colorSelectorLabelElement = $this->webDriver->findElement($colorSelectorLabelSearch);
        $color = $colorSelectorLabelElement->getText();
        $this->assertSame(self::COLOR, $color);
        $sizeLabelSearch = WebDriverBy::id('select_label_size');
        $sizeLabelElement = $this->webDriver->findElement($sizeLabelSearch);
        $size = $sizeLabelElement->getText();
        $this->assertSame(self::SIZE, $size);
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

    /**
     * Fill the shipping method information
     */
    public function fillPaymentMethod()
    {
        $this->findById('p_method_paylater')->click();
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::className('PmtSimulator')
            )
        );
        $this->assertTrue((bool) WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::className('PmtSimulator')
        ));
        $this->webDriver->executeScript("payment.save()");
        $reviewStepSearch = WebDriverBy::id('review-buttons-container');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($reviewStepSearch)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::visibilityOfElementLocated($reviewStepSearch)
        );
    }


    /**
     * Complete order and open PMT (redirect or iframe methods)
     *
     * @param bool $useIframe
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function goToPMT($useIframe = true)
    {
        sleep(5);
        $this->webDriver->executeScript('review.save()');

        // If use iFrame the test will end without finish the buy and test return
        if($useIframe) {
            sleep(10);
            $firstIframe = $this->webDriver->findElement(WebDriverBy::cssSelector("*[data-iframe-type='modal']"));
            $condition = WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($firstIframe);
            $this->webDriver->wait()->until($condition);
            $this->assertTrue((bool) $condition);

            $pmtModal = WebDriverBy::id('pmtmodal');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($pmtModal);
            $this->webDriver->wait()->until($condition);
            $this->assertTrue((bool) $condition);

            $iFrame = 'pmtmodal_iframe';
            $condition = WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($iFrame);
            $this->webDriver->wait()->until($condition);
            $this->assertTrue((bool) $condition);

            $this->logoutFromPmt();
            $this->webDriver->wait()->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::name('form-continue')
                )
            );
            $this->assertContains(
                'compra',
                $this->findByClass('Form-heading1')->getText()
            );
            // PMT opened successfully in iframe mode
            return;
        }
    }

    /**
     * Close previous pmt session if an user is logged in
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function logoutFromPmt()
    {
        // Wait the page to render (check the simulator is rendered)
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('minusButton')
            )
        );
        // Check if user is logged in in PMT
        $closeSession = $this->webDriver->findElements(WebDriverBy::name('one_click_return_to_normal'));
        if (count($closeSession) !== 0) {
            //Logged out
            $continueButtonSearch = WebDriverBy::name('one_click_return_to_normal');
            $continueButtonElement = $this->webDriver->findElement($continueButtonSearch);
            $continueButtonElement->click();
        }
    }

    /**
     * Fill the shipping method information
     */
    public function fillShippingMethod()
    {
        $this->findById('s_method_flatrate_flatrate')->click();
        $this->webDriver->executeScript('shippingMethod.save()');

        sleep(10);

        $checkoutStepPaymentMethodSearch = WebDriverBy::id('checkout-payment-method-load');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepPaymentMethodSearch)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepPaymentMethodSearch)
        );
    }

    /**
     * Verify That UTF Encoding is working
     */
    public function verifyUTF8()
    {
        $paymentFormElement = WebDriverBy::className('FieldsPreview-desc');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paymentFormElement);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->assertSame(
            $this->configuration['firstname'] . ' ' . $this->configuration['lastname'],
            $this->findByClass('FieldsPreview-desc')->getText()
        );
    }

    /**
     * Check if the purchase was in the myAccount panel and with Processing status
     *
     * @param string $statusText
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function checkLastPurchaseStatus($statusText = 'Processing')
    {
        $accountMenu = WebDriverBy::cssSelector('.account-cart-wrapper a.skip-link.skip-account');
        $this->clickElement($accountMenu);

        $myAccountMenu = WebDriverBy::cssSelector('#header-account .first a');
        $this->clickElement($myAccountMenu);

        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.box-account.box-recent')
            )
        );

        $status = $this->findByCss('.box-account.box-recent .data-table.orders .first .status em')->getText();
        $this->assertTrue(($status == $statusText));
    }

    /**
     * Check purchase return message
     *
     * @param string $message
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function checkPurchaseReturn($message = '')
    {
        // Check if all goes good
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.page-title h1')
            )
        );
        $successMessage = $this->findByCss('.page-title h1');
        $this->assertContains(
            $message,
            $successMessage->getText()
        );
    }
}
