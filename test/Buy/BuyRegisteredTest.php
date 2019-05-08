<?php

namespace Test\Buy;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Pagantis\ModuleUtils\Exception\AlreadyProcessedException;
use Pagantis\ModuleUtils\Exception\NoIdentificationException;
use Pagantis\ModuleUtils\Exception\QuoteNotFoundException;
use Pagantis\SeleniumFormUtils\SeleniumHelper;
use Httpful\Request;

/**
 * Class BuyRegisteredTest
 * @package Test\Buy
 *
 * @group magento-buy-registered
 */
class BuyRegisteredTest extends AbstractBuy
{
    /**
     * @var String $orderUrl
     */
    public $orderUrl;

    /**
     * @var array $configs
     */
    protected $configs = array(
        "PAGANTIS_TITLE",
        "PAGANTIS_SIMULATOR_DISPLAY_TYPE",
        "PAGANTIS_SIMULATOR_DISPLAY_SKIN",
        "PAGANTIS_SIMULATOR_DISPLAY_POSITION",
        "PAGANTIS_SIMULATOR_START_INSTALLMENTS",
        "PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR",
        "PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION",
        "PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR",
        "PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR",
        "PAGANTIS_FORM_DISPLAY_TYPE",
        "PAGANTIS_DISPLAY_MIN_AMOUNT",
        "PAGANTIS_URL_OK",
        "PAGANTIS_URL_KO",
    );

    /**
     * Test Buy Registered
     */
    public function testBuyRegistered()
    {
        $this->prepareProductAndCheckout();
        $this->login();
        $this->fillBillingInformation();
        $this->fillShippingMethod();
        $this->fillPaymentMethod();

        // get cart total price
        $cartPrice = WebDriverBy::cssSelector('#checkout-review-table tfoot tr.last .price');
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                $cartPrice
            )
        );
        $cartPrice = $this->webDriver->findElement($cartPrice)->getText();
        // --------------------
        $this->goToPagantis();
        $this->verifyPagantis();
        $this->commitPurchase();
        $this->checkPurchaseReturn(self::CORRECT_PURCHASE_MESSAGE);
        $this->checkLastPurchaseStatus('Processing');

        // get registered purchase amount
        $checkoutPrice = WebDriverBy::cssSelector(
            '.box-account.box-recent .data-table.orders .first .total .price'
        );
        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                $checkoutPrice
            )
        );
        $checkoutPrice = $this->webDriver->findElement($checkoutPrice)->getText();
        //----------------------

        $this->assertTrue(($cartPrice == $checkoutPrice));
        $this->makeValidation();
        $this->quit();
    }

    /**
     * Fill the billing information
     */
    public function fillBillingInformation()
    {
        $this->webDriver->executeScript('billing.save()');
        $checkoutStepShippingMethodSearch = WebDriverBy::id('checkout-shipping-method-load');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($checkoutStepShippingMethodSearch);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Login
     */
    public function login()
    {
        $this->findById('login-email')->clear()->sendKeys($this->configuration['email']);
        $this->findById('login-password')->clear()->sendKeys($this->configuration['password']);
        $this->findById('login-form')->submit();

        $billingAddressSelector = WebDriverBy::id('billing-address-select');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($billingAddressSelector);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Commit Purchase
     * @throws \Exception
     */
    public function commitPurchase()
    {

        $condition = WebDriverExpectedCondition::titleContains(self::PAGANTIS_TITLE);
        $this->webDriver->wait(300)->until($condition, $this->webDriver->getCurrentURL());
        $this->assertTrue((bool)$condition, "PR32");

        // complete the purchase with redirect
        SeleniumHelper::finishForm($this->webDriver);
    }

    /**
     * Verify Pagantis
     *
     * @throws \Exception
     */
    public function verifyPagantis()
    {
        $condition = WebDriverExpectedCondition::titleContains(self::PAGANTIS_TITLE);
        $this->webDriver->wait(300)->until($condition, $this->webDriver->getCurrentURL());
        $this->assertTrue((bool)$condition, $this->webDriver->getCurrentURL());
    }

    public function makeValidation()
    {
        $this->checkConcurrency();
        $this->checkPagantisOrderId();
        $this->checkAlreadyProcessed();
        $this->checkLog();
        $this->checkExtraConfig();
    }


    /**
     * Check if with a empty parameter called order-received we can get a QuoteNotFoundException
     */
    protected function checkConcurrency()
    {
        $notifyUrl = self::MAGENTO_URL.self::NOTIFICATION_FOLDER.'?order=';
        $this->assertNotEmpty($notifyUrl, $notifyUrl);
        $response = Request::post($notifyUrl)->expects('json')->send();
        $this->assertNotEmpty($response->body->result, $response);
        $this->assertNotEmpty($response->body->status_code, $response);
        $this->assertNotEmpty($response->body->timestamp, $response);
        $this->assertContains(
            QuoteNotFoundException::ERROR_MESSAGE,
            $response->body->result,
            "PR53=>".$response->body->result
        );
    }

    /**
     * Check if with a parameter called order-received set to a invalid identification,
     * we can get a NoIdentificationException
     */
    protected function checkPagantisOrderId()
    {
        $orderId=0;
        $notifyUrl = self::MAGENTO_URL.self::NOTIFICATION_FOLDER.'?order='.$orderId;
        $this->assertNotEmpty($notifyUrl, $notifyUrl);
        $response = Request::post($notifyUrl)->expects('json')->send();
        $this->assertNotEmpty($response->body->result, $response);
        $this->assertNotEmpty($response->body->status_code, $response);
        $this->assertNotEmpty($response->body->timestamp, $response);
        $this->assertEquals(
            $response->body->merchant_order_id,
            $orderId,
            $response->body->merchant_order_id.'!='. $orderId
        );
        $this->assertContains(
            NoIdentificationException::ERROR_MESSAGE,
            $response->body->result,
            "PR58=>".$response->body->result
        );
    }

    /**
     * Check if re-launching the notification we can get a AlreadyProcessedException
     *
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    protected function checkAlreadyProcessed()
    {
        $notifyUrl = self::MAGENTO_URL.self::NOTIFICATION_FOLDER.'?order=145000008';
        $response = Request::post($notifyUrl)->expects('json')->send();
        $this->assertNotEmpty($response->body->result, $response);
        $this->assertNotEmpty($response->body->status_code, $response);
        $this->assertNotEmpty($response->body->timestamp, $response);
        $this->assertNotEmpty($response->body->merchant_order_id, $response);
        $this->assertNotEmpty($response->body->pagantis_order_id, $response);
        $this->assertContains(
            AlreadyProcessedException::ERROR_MESSAGE,
            $response->body->result,
            "PR51=>".$response->body->result
        );
    }

    /**
     * Check if log controller is writing the launched exceptions
     */
    protected function checkLog()
    {
        $logUrl = self::MAGENTO_URL.self::LOG_FOLDER;
        $response = Request::get($logUrl)->expects('json')->send();
        $this->assertEquals(3, count($response->body), "PR57=>".$logUrl." = ".count($response->body));
    }

    /**
     * Check if config controller is configured and it works
     */
    protected function checkExtraConfig()
    {
        $configUrl = self::MAGENTO_URL.self::CONFIG_FOLDER;

        $response = Request::get($configUrl)->expects('json')->send();
        $content = $response->body;
        foreach ($this->configs as $config) {
            $this->assertArrayHasKey($config, (array) $content, "PR61=>".print_r($content, true));
        }

        $defaultTitle = $content->PAGANTIS_TITLE;
        $newTitle = 'changed';
        $body = array('PAGANTIS_TITLE' => $newTitle);
        $response = Request::post($configUrl)
                           ->body($body, Mime::FORM)
                           ->expectsJSON()
                           ->send();
        $title = $response->body->PAGANTIS_TITLE;
        $this->assertEquals($newTitle, $title, "PR62=>".$configUrl." = ".$title);

        $body = array('PAGANTIS_TITLE' => $defaultTitle);
        $response = Request::post($configUrl)
                           ->body($body, Mime::FORM)
                           ->expectsJSON()
                           ->send();
        $title = $response->body->PAGANTIS_TITLE;
        $this->assertEquals($defaultTitle, $title, "PR62=>".$configUrl." = ".$title);
    }
}
