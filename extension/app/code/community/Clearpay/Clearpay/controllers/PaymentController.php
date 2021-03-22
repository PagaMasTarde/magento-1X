dfsffsdf<?php

require_once(__DIR__.'/../../../../../../lib/Clearpay/autoload.php');
require_once(__DIR__.'/AbstractController.php');

use Afterpay\SDK\HTTP\Request\CreateCheckout;
use Afterpay\SDK\MerchantAccount as ClearpayMerchantAccount;

/**
 * Class Clearpay_Clearpay_PaymentController
 */
class Clearpay_Clearpay_PaymentController extends AbstractController
{
    /**
     * @var integer $magentoOrderId
     */
    protected $magentoOrderId;

    /**
     * @var Mage_Sales_Model_Order $magentoOrder
     */
    protected $magentoOrder;

    /**
     * @var Mage_Customer_Model_Session $customer
     */
    protected $customer;

    /**
     * @var Mage_Sales_Model_Order $order
     */
    protected $order;

    /**
     * @var Mage_Sales_Model_Order_Item $itemCollection
     */
    protected $itemCollection;

    /**
     * @var Mage_Sales_Model_Order $addressCollection
     */
    protected $addressCollection;

    /**
     * @var String $publicKey
     */
    protected $publicKey;

    /**
     * @var String $privateKey
     */
    protected $privateKey;

    /**
     * @var String $environment
     */
    protected $environment;

    /**
     * @var String $currency
     */
    protected $currency;

    /**
     * @var string magentoOrderData
     */
    protected $magentoOrderData;

    /**
     * @var mixed $addressData
     */
    protected $addressData;

    /**
     * @var string $redirectOkUrl
     */
    protected $redirectOkUrl;

    /**
     * @var string $notificationOkUrl
     */
    protected $notificationOkUrl;

    /**
     * @var string $cancelUrl
     */
    protected $cancelUrl;

    /**
     * @var string $urlToken
     */
    protected $urlToken;

    /**
     * Find and init variables needed to process payment
     */
    public function prepareVariables()
    {
        $checkoutSession = Mage::getSingleton('checkout/session');
        $this->magentoOrderId = $checkoutSession->getLastRealOrderId();

        $salesOrder = Mage::getModel('sales/order');
        $this->magentoOrder = $salesOrder->loadByIncrementId($this->magentoOrderId);

        $mageCore = Mage::helper('core');
        $this->magentoOrderData = json_decode($mageCore->jsonEncode($this->magentoOrder->getData()), true);
        $this->urlToken = strtoupper(md5(uniqid(rand(), true)));
        $this->redirectOkUrl = Mage::getUrl(
            'clearpay/notify',
            array(
                '_query' => array(
                    'token' => $this->urlToken,
                    'origin' => 'redirect',
                    'order' => $this->magentoOrderData['increment_id']
                )
            )
        );
        $this->notificationOkUrl = Mage::getUrl(
            'clearpay/notify',
            array(
                '_query' => array(
                    'token' => $this->urlToken,
                    'origin' => 'notification',
                    'order' => $this->magentoOrderData['increment_id']
                )
            )
        );
        $this->cancelUrl = Mage::getUrl(
            'clearpay/notify/cancel',
            array('_query' => array('token' => $this->urlToken, 'order' => $this->magentoOrderData['increment_id']))
        );

        $this->itemCollection = $this->magentoOrder->getAllVisibleItems();
        $addressCollection = $this->magentoOrder->getAddressesCollection();
        $this->addressData = $addressCollection->getData();

        $customerSession = Mage::getSingleton('customer/session');
        $this->customer = $customerSession->getCustomer();

        $moduleConfig = Mage::getStoreConfig('payment/clearpay');
        $this->publicKey = $moduleConfig['clearpay_merchant_id'];
        $this->privateKey = $moduleConfig['clearpay_secret_key'];
        $this->environment = $moduleConfig['clearpay_environment'];
        $this->currency = Mage::app()->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Default Action controller, launch in a new purchase show Clearpay Form
     */
    public function indexAction()
    {
        $this->prepareVariables();
        if ($this->magentoOrder->getStatus() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            return $this->_redirectUrl($this->redirectOkUrl);
        }

        $node = Mage::getConfig()->getNode();
        $email = $this->customer->email ? $this->customer->email : $this->magentoOrderData['customer_email'];
        $fullName = null;
        $telephone = null;
        $userAddress = null;
        $orderShippingAddress = null;
        $orderBillingAddress = null;
        $mgShippingAddress = null;
        $mgBillingAddress = null;
        $shippingFirstName = null;
        $shippingLastName = null;
        $shippingTelephone = null;
        $shippingAddress = null;
        $shippingCity = null;
        $shippingPostCode = null;
        $shippingCountryId = null;
        $mgBillingAddress = null;
        $billingFirstName = null;
        $billingLastName = null;
        $billingTelephone = null;
        $billingAddress = null;
        $billingCity = null;
        $billingPostCode = null;
        $billingCountryId = null;
        try {
            for ($i = 0; $i <= count($this->addressData); $i++) {
                if (isset($this->addressData[$i]) && array_search('shipping', $this->addressData[$i])) {
                    $mgShippingAddress = $this->addressData[$i];
                    $shippingFirstName = $mgShippingAddress['firstname'];
                    $shippingLastName = $mgShippingAddress['lastname'];
                    $shippingTelephone = $mgShippingAddress['telephone'];
                    $shippingAddress = $mgShippingAddress['street'];
                    $shippingCity = $mgShippingAddress['city'];
                    $shippingPostCode = $mgShippingAddress['postcode'];
                    $shippingCountryId = $mgShippingAddress['country_id'];
                }
                if (isset($this->addressData[$i]) && array_search('billing', $this->addressData[$i])) {
                    $mgBillingAddress = $this->addressData[$i];
                    $billingFirstName = $mgBillingAddress['firstname'];
                    $billingLastName = $mgBillingAddress['lastname'];
                    $billingTelephone = $mgBillingAddress['telephone'];
                    $billingAddress = $mgBillingAddress['street'];
                    $billingCity = $mgBillingAddress['city'];
                    $billingPostCode = $mgBillingAddress['postcode'];
                    $billingCountryId = $mgBillingAddress['country_id'];
                }
            }
            \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
            $createCheckoutRequest = new CreateCheckout();
            $clearpayMerchantAccount = new ClearpayMerchantAccount();
            $clearpayMerchantAccount
                ->setMerchantId($this->publicKey)
                ->setSecretKey($this->privateKey)
                ->setApiEnvironment($this->environment);
            if (!is_null($shippingCountryId)) {
                $clearpayMerchantAccount->setCountryCode($shippingCountryId);
            }

            $createCheckoutRequest
                ->setMerchant(array(
                    'redirectConfirmUrl' => $this->redirectOkUrl,
                    'redirectCancelUrl' => $this->cancelUrl
                ))
                ->setMerchantAccount($clearpayMerchantAccount)
                ->setTotalAmount(
                    $this->parseAmount($this->magentoOrder->getGrandTotal()),
                    $this->currency
                )
                ->setTaxAmount(
                    $this->parseAmount(
                        $this->magentoOrder->getGrandTotal() - $this->magentoOrder->getTaxAmount()
                    ),
                    $this->currency
                )
                ->setConsumer(array(
                    'phoneNumber' => $shippingTelephone,
                    'givenNames' => $shippingFirstName,
                    'surname' => $shippingLastName,
                    'email' => $email
                ))
                ->setBilling(array(
                    'name' => $billingFirstName . " " . $billingLastName,
                    'line1' => $billingAddress,
                    'suburb' => $billingCity,
                    'state' => '',
                    'postcode' => $billingPostCode,
                    'countryCode' => $billingCountryId,
                    'phoneNumber' => $billingTelephone
                ))
                ->setShipping(array(
                    'name' => $shippingFirstName . " " . $shippingLastName,
                    'line1' => $shippingAddress,
                    'suburb' => $shippingCity,
                    'state' => '',
                    'postcode' => $shippingPostCode,
                    'countryCode' => $shippingCountryId,
                    'phoneNumber' => $shippingTelephone
                ))
                ->setShippingAmount(
                    $this->parseAmount($this->magentoOrder->getShippingAmount()),
                    $this->currency
                )
                ->setCourier(array(
                    'shippedAt' => '',
                    'name' => (string)$this->magentoOrder->getShippingMethod(),
                    'tracking' => '',
                    'priority' => 'STANDARD'
                ));

            $discountAmount = $this->magentoOrder->getDiscountAmount();
            if (!empty($discountAmount)) {
                $createCheckoutRequest->setDiscounts(array(
                    array(
                        'displayName' => 'Shop discount',
                        'amount' => array($this->parseAmount($discountAmount), $this->currency)
                    )
                ));
            }

            $products = array();
            foreach ($this->itemCollection as $item) {
                $products[] = array(
                    'name' => $item->getName(),
                    'quantity' => $item->getQtyToShip(),
                    'price' => array(
                        $this->parseAmount($item->getRowTotalInclTax())),
                    $this->currency
                );
            }
            $createCheckoutRequest->setItems($products);

            $header = 'Magento 1.x/' . (string)$node->modules->Clearpay_Clearpay->version
                . '(Magento/' . Mage::getVersion() . '; PHP/' . phpversion() . '; Merchant/' . $this->publicKey
                . ') ' . Mage::getBaseUrl();
            $createCheckoutRequest->addHeader('User-Agent', $header);
            $createCheckoutRequest->addHeader('Country', $shippingCountryId);
            $url = $cancelUrl;
            if ($createCheckoutRequest->isValid()) {
                $createCheckoutRequest->send();
                $errorMessage = 'empty response';
                if ($createCheckoutRequest->getResponse()->getHttpStatusCode() >= 400
                    || isset($createCheckoutRequest->getResponse()->getParsedBody()->errorCode)
                ) {
                    if (isset($createCheckoutRequest->getResponse()->getParsedBody()->message)) {
                        $errorMessage = $createCheckoutRequest->getResponse()->getParsedBody()->message;
                    }
                    $errorMessage .= '. Status code: ' . $createCheckoutRequest->getResponse()->getHttpStatusCode();
                    $this->saveLog('Error received when trying to create a order: ' . $errorMessage);
                } else {
                    try {
                        $url = $createCheckoutRequest->getResponse()->getParsedBody()->redirectCheckoutUrl;
                        $this->insertOrderControl(
                            $this->magentoOrderId,
                            $createCheckoutRequest->getResponse()->getParsedBody()->token,
                            $this->urlToken
                        );
                    } catch (\Exception $exception) {
                        $this->saveLog($exception->getMessage());
                        $url = $cancelUrl;
                    }
                }
            } else {
                $this->saveLog(json_encode($createCheckoutRequest->getValidationErrors()));
            }
        } catch (Exception $exception) {
            $this->saveLog($exception->getMessage());
            return $this->_redirectUrl($this->cancelUrl);
        }

        try {
            return $this->_redirectUrl($url);
        } catch (Exception $exception) {
            $this->saveLog($exception->getMessage());
            return $this->_redirectUrl($this->cancelUrl);
        }
    }

    /**
     * Create a record in AbstractController::Clearpay_ORDERS_TABLE to match the merchant order with the clearpay order
     *
     * @param string $magentoOrderId
     * @param string $clearpayOrderId
     * @param string $token
     *
     * @throws Exception
     */
    private function insertOrderControl($magentoOrderId, $clearpayOrderId, $token)
    {
        $model = Mage::getModel('clearpay/order');
        $model->setData(array(
            'clearpay_order_id' => $clearpayOrderId,
            'mg_order_id' => $magentoOrderId,
            'token' => $token,
        ));
        $model->save();
    }

    /**
     * @param null $amount
     * @return string
     */
    public function parseAmount($amount = null)
    {
        return number_format(
            round($amount, 2, PHP_ROUND_HALF_UP),
            2,
            '.',
            ''
        );
    }
}