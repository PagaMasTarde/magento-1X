<?php

require_once(__DIR__.'/../../../../../../lib/Clearpay/autoload.php');
require_once(__DIR__.'/AbstractController.php');

use Clearpay\OrdersApiClient\Model\Order as ClearpayModelOrder;
use Clearpay\OrdersApiClient\Model\Order\User as ClearpayModelOrderUser;
use Clearpay\OrdersApiClient\Model\Order\User\Address as ClearpayModelOrderAddress;
use Clearpay\OrdersApiClient\Model\Order\User\OrderHistory as ClearpayModelOrderHistory;
use Clearpay\OrdersApiClient\Model\Order\Metadata as ClearpayModelOrderMetadata;
use Clearpay\OrdersApiClient\Model\Order\ShoppingCart as ClearpayModelOrderShoppingCart;
use Clearpay\OrdersApiClient\Model\Order\ShoppingCart\Details as ClearpayModelOrderShoppingCartDetails;
use Clearpay\OrdersApiClient\Model\Order\ShoppingCart\Details\Product as ClearpayModelOrderShoppingCartProduct;
use Clearpay\OrdersApiClient\Model\Order\Configuration as ClearpayModelOrderConfiguration;
use Clearpay\OrdersApiClient\Model\Order\Configuration\Urls as ClearpayModelOrderUrls;
use Clearpay\OrdersApiClient\Model\Order\Configuration\Channel as ClearpayModelOrderChannel;
use Clearpay\OrdersApiClient\Client as ClearpayClient;

use Clearpay\ModuleUtils\Exception\OrderNotFoundException;

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
            array('_query' => array('token' => $this->urlToken, 'origin' => 'redirect', 'order' => $this->magentoOrderData['increment_id']))
        );
        $this->notificationOkUrl = Mage::getUrl(
            'clearpay/notify',
            array('_query' => array('token' => $this->urlToken, 'origin' => 'notification', 'order' => $this->magentoOrderData['increment_id']))
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
        $extraConfig = Mage::helper('clearpay/ExtraConfig')->getExtraConfig();
        $this->publicKey = $moduleConfig['clearpay_public_key'];
        $this->privateKey = $moduleConfig['clearpay_private_key'];
        $this->iframe = $extraConfig['CLEARPAY_FORM_DISPLAY_TYPE'];
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
        $metadata = array(
            'pg_module' => 'magento1x',
            'pg_version' => (string)$node->modules->Clearpay_Clearpay->version,
            'ec_module' => 'magento',
            'ec_version' => Mage::getVersion(),
            'php' => phpversion(),
            'member_since' => $this->customer->getCreatedAt(),
        );
        $fullName = null;
        $telephone = null;
        $userAddress = null;
        $orderShippingAddress = null;
        $orderBillingAddress = null;
        $mgShippingAddress = null;
        $mgBillingAddress = null;
        try {
            for ($i = 0; $i <= count($this->addressData); $i++) {
                if (isset($this->addressData[$i]) && array_search('shipping', $this->addressData[$i])) {
                    $mgShippingAddress = $this->addressData[$i];
                    $fullName = $mgShippingAddress['firstname'] . ' ' . $mgShippingAddress['lastname'];
                    $telephone = $mgShippingAddress['telephone'];
                    $userAddress = new ClearpayModelOrderAddress();
                    $userAddress
                        ->setZipCode($mgShippingAddress['postcode'])
                        ->setFullName($fullName)
                        ->setCountryCode($mgShippingAddress['country_id'])
                        ->setCity($mgShippingAddress['city'])
                        ->setAddress($mgShippingAddress['street'])
                        ->setFixPhone($telephone)
                        ->setMobilePhone($telephone)
                        ->setNationalId($this->getNationalId($mgShippingAddress, null))
                        ->setTaxId($this->getTaxId($mgShippingAddress, null))
                        ->setDni($this->getDni($mgShippingAddress))
                    ;
                    $orderShippingAddress = new ClearpayModelOrderAddress();
                    $orderShippingAddress
                        ->setZipCode($mgShippingAddress['postcode'])
                        ->setFullName($fullName)
                        ->setCountryCode($mgShippingAddress['country_id'])
                        ->setCity($mgShippingAddress['city'])
                        ->setAddress($mgShippingAddress['street'])
                        ->setFixPhone($telephone)
                        ->setMobilePhone($telephone)
                        ->setNationalId($this->getNationalId($mgShippingAddress, null))
                        ->setTaxId($this->getTaxId($mgShippingAddress, null))
                        ->setDni($this->getDni($mgShippingAddress))
                    ;
                }
                if (isset($this->addressData[$i]) && array_search('billing', $this->addressData[$i])) {
                    $mgBillingAddress = $this->addressData[$i];
                    $orderBillingAddress = new ClearpayModelOrderAddress();
                    $orderBillingAddress
                        ->setZipCode($mgBillingAddress['postcode'])
                        ->setFullName(
                            $mgBillingAddress['firstname'] . ' ' .
                            $mgBillingAddress['lastname']
                        )
                        ->setCountryCode($mgBillingAddress['country_id'])
                        ->setCity($mgBillingAddress['city'])
                        ->setAddress($mgBillingAddress['street'])
                        ->setMobilePhone($mgBillingAddress['telephone'])
                        ->setFixPhone($mgBillingAddress['telephone'])
                        ->setNationalId($this->getNationalId(null, $mgBillingAddress))
                        ->setTaxId($this->getTaxId(null, $mgBillingAddress))
                        ->setDni($this->getDni($mgShippingAddress))
                    ;
                }
            }

            if (is_null($fullName)) {
                $firstName = 'not setted';
                if (isset($this->magentoOrderData['customer_firstname'])) {
                    $firstName = $this->magentoOrderData['customer_firstname'];
                }

                $lastName = 'not setted';
                if (isset($this->magentoOrderData['customer_lastname'])) {
                    $lastName = $this->magentoOrderData['customer_lastname'];
                }

                $fullName = $firstName . ' ' . $lastName;
            }

            if (is_null($telephone)) {
                $addr =  $this->customer->getPrimaryShippingAddress();
                $telephone = $addr->getTelephone();
            }
            $email = $this->customer->email ? $this->customer->email : $this->magentoOrderData['customer_email'];
            $orderUser = new ClearpayModelOrderUser();

            // Hook. This will be deleted when orders validate the empty address as a correct field.
            if (is_null($orderShippingAddress)) {
                $orderShippingAddress = $orderBillingAddress;
            }
            if (is_null($userAddress)) {
                $userAddress = $orderBillingAddress;
            }
            // -------------------------------------------------------------------------------------

            $orderUser
                ->setFullName($fullName)
                ->setDateOfBirth($this->customer->dob)
                ->setEmail($email)
                ->setMobilePhone($telephone)
                ->setAddress($userAddress)
                ->setShippingAddress($orderShippingAddress)
                ->setBillingAddress($orderBillingAddress)
                ->setNationalId($this->getNationalId($mgShippingAddress, $mgBillingAddress))
                ->setTaxId($this->getTaxId($mgShippingAddress, $mgBillingAddress))
                ->setDni($this->getDni($mgShippingAddress))
            ;

            $orderCollection = Mage::getModel('sales/order')->getCollection();
            $orderCollection = $orderCollection->addFieldToFilter('customer_id', $this->customer->getId());
            foreach ($orderCollection as $cOrder) {
                if ($cOrder->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                    $orderHistory = new ClearpayModelOrderHistory();
                    $orderHistory
                        ->setAmount(floatval($cOrder->getGrandTotal())*100)
                        ->setDate($cOrder->getCreatedAt());
                    $orderUser->addOrderHistory($orderHistory);
                }
            }

            $promotedAmount = 0;
            $details = new ClearpayModelOrderShoppingCartDetails();
            $details->setShippingCost(floatval($this->magentoOrder->getShippingAmount())*100);
            foreach ($this->itemCollection as $item) {
                $catalogProduct = Mage::getModel('catalog/product')->load($item->getId());
                $attributes = $catalogProduct->getAttributes();

                $product = new ClearpayModelOrderShoppingCartProduct();
                $product
                    ->setAmount(floatval($item->getRowTotalInclTax())*100)
                    ->setQuantity($item->getQtyToShip())
                    ->setDescription($item->getName());
                $details->addProduct($product);

                $attributesobj = $attributes["clearpay_promoted"];
                $clearpayPromoted = $attributesobj->getFrontend()->getValue($catalogProduct) == "Si" ? 1 : 0;

                $productPrice = floatval($item->getRowTotalInclTax())*100;
                if ($clearpayPromoted) {
                    $metadata[$item->getId()] =
                        'Promoted Item: ' . $item->getName() .
                        ' Price: ' . floatval($item->getRowTotalInclTax())*100 .
                        ' Qty: ' . $item->getQtyToShip() .
                        ' Item ID: ' . $item->getId();
                    $promotedAmount += $productPrice;

                }
            }

            $orderShoppingCart = new ClearpayModelOrderShoppingCart();
            $orderShoppingCart
                ->setDetails($details)
                ->setOrderReference($this->magentoOrderId)
                ->setPromotedAmount($promotedAmount)
                ->setTotalAmount((string) floor(100 * $this->magentoOrder->getGrandTotal()));

            $orderConfigurationUrls = new ClearpayModelOrderUrls();
            $orderConfigurationUrls
                ->setOk($this->redirectOkUrl)
                ->setCancel($this->cancelUrl)
                ->setKo($this->redirectOkUrl)
                ->setAuthorizedNotificationCallback($this->notificationOkUrl)
                ->setRejectedNotificationCallback(null);

            $orderChannel = new ClearpayModelOrderChannel();
            $orderChannel
                ->setAssistedSale(false)
                ->setType(ClearpayModelOrderChannel::ONLINE)
            ;

            $extraConfig = Mage::helper('clearpay/ExtraConfig')->getExtraConfig();
            $allowedCountries = unserialize($extraConfig['CLEARPAY_ALLOWED_COUNTRIES']);
            $langCountry = substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2);
            $shippingCountry = $mgShippingAddress['country_id'];
            $billingCountry = $mgBillingAddress['country_id'];
            $purchaseCountry =
                in_array(strtolower($langCountry), $allowedCountries) ? $langCountry :
                in_array(strtolower($shippingCountry), $allowedCountries) ? $shippingCountry :
                in_array(strtolower($billingCountry), $allowedCountries) ? $billingCountry : null;

            $orderConfiguration = new ClearpayModelOrderConfiguration();
            $orderConfiguration
                ->setChannel($orderChannel)
                ->setUrls($orderConfigurationUrls)
                ->setPurchaseCountry($purchaseCountry)
            ;

            $metadataOrder = new ClearpayModelOrderMetadata();
            foreach ($metadata as $key => $metadatum) {
                $metadataOrder->addMetadata($key, $metadatum);
            }

            $order = new ClearpayModelOrder();
            $order
                ->setConfiguration($orderConfiguration)
                ->setMetadata($metadataOrder)
                ->setShoppingCart($orderShoppingCart)
                ->setUser($orderUser);
        } catch (Exception $exception) {
            $this->saveLog($exception);
            return $this->_redirectUrl($this->cancelUrl);
        }


        try {
            $orderClient = new ClearpayClient(
                $this->publicKey,
                $this->privateKey
            );
            $order = $orderClient->createOrder($order);
            if ($order instanceof ClearpayModelOrder) {
                $url = $order->getActionUrls()->getForm();
                $this->insertOrderControl($this->magentoOrderId, $order->getId(), $this->urlToken);
            } else {
                throw new OrderNotFoundException();
            }
        } catch (Exception $exception) {
            $this->order = $order;
            $this->saveLog($exception);
            return $this->_redirectUrl($this->cancelUrl);
        }

        if (!$this->iframe) {
            try {
                return $this->_redirectUrl($url);
            } catch (Exception $exception) {
                $this->saveLog($exception);
                return $this->_redirectUrl($this->cancelUrl);
            }
        }

        try {
            /** @var Mage_Core_Block_Template $block */
            $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'custompaymentmethod',
                array('template' => 'clearpay/payment/iframe.phtml')
            );

            $this->loadLayout();
            $block->assign(array(
                'orderUrl' => $url,
                'checkoutUrl' => $this->cancelUrl,
                'leaveMessage' => $this->__('Are you sure you want to leave?')
            ));
            $this->getLayout()->getBlock('content')->append($block);
        } catch (Exception $exception) {
            $this->saveLog($exception);
            return $this->_redirectUrl($this->cancelUrl);
        }

        return $this->renderLayout();
    }

    /**
     * Create a record in AbstractController::CLEARPAY_ORDERS_TABLE to match the merchant order with the clearpay order
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
     * @param null $shippingAddress
     * @param null $billigAddress
     * @return mixed|null
     */
    private function getNationalId($shippingAddress = null, $billigAddress = null)
    {
        if (isset($this->customer->national_id)) {
            return $this->customer->national_id;
        } elseif ($billigAddress !== null and isset($billigAddress['national_id'])) {
            return $billigAddress['national_id'];
        } elseif ($shippingAddress !== null and isset($shippingAddress['national_id'])) {
            return $shippingAddress['national_id'];
        } else {
            return null;
        }
    }

    /**
     * @param null $shippingAddress
     * @param null $billigAddress
     * @return mixed|null
     */
    private function getTaxId($shippingAddress = null, $billigAddress = null)
    {
        if (isset($this->customer->tax_id)) {
            return $this->customer->tax_id;
        } elseif (isset($this->customer->privatecompany_fiscalcode)) {
            return $this->customer->privatecompany_fiscalcode;
        } elseif ($billigAddress !== null and isset($billigAddress['tax_id'])) {
            return $billigAddress['tax_id'];
        } elseif ($shippingAddress !== null and isset($shippingAddress['tax_id'])) {
            return $shippingAddress['tax_id'];
        } else {
            return null;
        }
    }

    /**
     * @param null $shippingAddress
     * @return mixed|null
     */
    private function getDni($shippingAddress = null)
    {
        if (isset($this->customer->dni)) {
            return $this->customer->dni;
        } elseif (isset($this->customer->nif)) {
            return $this->customer->nif;
        } elseif ($shippingAddress !== null and isset($shippingAddress['dni'])) {
            return $shippingAddress['dni'];
        } else {
            return $this->getNationalId($shippingAddress);
        }
    }
}
