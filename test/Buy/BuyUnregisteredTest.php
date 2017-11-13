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
        $this->completeOrderAndGoToPMT();
    }

    /**
     * Complete order and check amounts
     */
    public function completeOrderAndGoToPMT()
    {
        //Continue to shipping, in this case shipping == billing
        $this->webDriver->executeScript('review.save()');

        $currentUrl = $this->webDriver->getCurrentURL();

        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt('iframe-pagantis')
        );
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('form-continue')
            )
        );

        $this->assertContains(
            'compra',
            $this->findByClass('Form-heading1')->getText()
        );
    }

    /**
     * Fill the shipping method information
     */
    public function fillShippingMethod()
    {
        $this->findById('s_method_flatrate_flatrate')->click();

        //Continue to shipping, in this case shipping == billing
        $this->webDriver->executeScript('shippingMethod.save()');

        //Verify
        $checkoutStepPaymentMethodSearch = WebDriverBy::id('checkout-payment-method-load');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepPaymentMethodSearch)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepPaymentMethodSearch)
        );
    }

    /**
     * Fill the shipping method information
     */
    public function fillPaymentMethod()
    {
        $this->findById('p_method_paylater')->click();

        //Verify simulator exists
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::className('PmtSimulator')
            )
        );
        $this->assertTrue((bool) WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::className('PmtSimulator')
        ));

        //Continue and Verify
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
     * Fill the billing information
     */
    public function fillBillingInformation()
    {
        // Fill the form
        $this->findById('billing:firstname')->sendKeys($this->configuration['firstname']);
        $this->findById('billing:lastname')->sendKeys($this->configuration['lastname']);
        $this->findById('billing:email')->sendKeys($this->configuration['email']);
        $this->findById('billing:street1')->sendKeys($this->configuration['street']);
        $this->findById('billing:city')->sendKeys($this->configuration['city']);
        $this->findById('billing:postcode')->sendKeys($this->configuration['zip']);
        $this->findById('billing:region_id')->sendKeys($this->configuration['city']);
        $this->findById('billing:telephone')->sendKeys($this->configuration['phone']);

        //Continue to shipping, in this case shipping == billing
        $this->webDriver->executeScript('billing.save()');

        //Verify
        $checkoutStepShippingMethodSearch = WebDriverBy::id('checkout-shipping-method-load');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepShippingMethodSearch)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepShippingMethodSearch)
        );
    }

    /**
     * Select checkout as guest and continue
     */
    public function selectGuestAndContinue()
    {
        //Checkout As Guest
        $formListSearch = WebDriverBy::className('form-list');
        $checkoutAsGuestSearch = $formListSearch->id('login:guest');
        $checkoutAsGuestElement = $this->webDriver->findElement($checkoutAsGuestSearch);
        $checkoutAsGuestElement->click();

        //Continue
        $continueButtonSearch = WebDriverBy::id('onepage-guest-register-button');
        $continueButtonElement = $this->webDriver->findElement($continueButtonSearch);
        $continueButtonElement->click();

        //Verify
        $checkoutStepBillingSearch = WebDriverBy::id('checkout-step-billing');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepBillingSearch)
        );
        $this->assertTrue(
            (bool) WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepBillingSearch)
        );
    }
}
