<?php

require_once(__DIR__.'/../../../../../../lib/Pagantis/autoload.php');
require_once(__DIR__.'/AbstractController.php');

use Pagantis\OrdersApiClient\Model\Order as PagantisModelOrder;
use Pagantis\OrdersApiClient\Model\Order\User as PagantisModelOrderUser;
use Pagantis\OrdersApiClient\Model\Order\User\Address as PagantisModelOrderAddress;
use Pagantis\OrdersApiClient\Model\Order\User\OrderHistory as PagantisModelOrderHistory;
use Pagantis\OrdersApiClient\Model\Order\Metadata as PagantisModelOrderMetadata;
use Pagantis\OrdersApiClient\Model\Order\ShoppingCart as PagantisModelOrderShoppingCart;
use Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details as PagantisModelOrderShoppingCartDetails;
use Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details\Product as PagantisModelOrderShoppingCartProduct;
use Pagantis\OrdersApiClient\Model\Order\Configuration as PagantisModelOrderConfiguration;
use Pagantis\OrdersApiClient\Model\Order\Configuration\Urls as PagantisModelOrderUrls;
use Pagantis\OrdersApiClient\Model\Order\Configuration\Channel as PagantisModelOrderChannel;
use Pagantis\OrdersApiClient\Client as PagantisClient;

use Pagantis\ModuleUtils\Exception\OrderNotFoundException;

/**
 * Class Pagantis_Pagantis_PaymentController
 */
class Pagantis_Pagantis_PaymentController extends AbstractController
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
        $this->okUrl = Mage::getUrl(
            'pagantis/notify',
            array('_query' => array('order' => $this->magentoOrderData['increment_id']))
        );
        $this->cancelUrl = Mage::getUrl(
            'pagantis/notify/cancel',
            array('_query' => array('order' => $this->magentoOrderData['increment_id']))
        );

        $this->itemCollection = $this->magentoOrder->getAllVisibleItems();
        $addressCollection = $this->magentoOrder->getAddressesCollection();
        $this->addressData = $addressCollection->getData();

        $customerSession = Mage::getSingleton('customer/session');
        $this->customer = $customerSession->getCustomer();

        $moduleConfig = Mage::getStoreConfig('payment/pagantis');
        $extraConfig = Mage::helper('pagantis/ExtraConfig')->getExtraConfig();
        $this->publicKey = $moduleConfig['pagantis_public_key'];
        $this->privateKey = $moduleConfig['pagantis_private_key'];
        $this->iframe = $extraConfig['PAGANTIS_FORM_DISPLAY_TYPE'];
    }

    /**
     * Default Action controller, launch in a new purchase show Pagantis Form
     */
    public function indexAction()
    {
        $this->prepareVariables();
        if ($this->magentoOrder->getStatus() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            return $this->_redirectUrl($this->okUrl);
        }

        $node = Mage::getConfig()->getNode();
        $metadata = array(
            'magento' => Mage::getVersion(),
            'pagantis' => (string)$node->modules->Pagantis_Pagantis->version,
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
                    $userAddress = new PagantisModelOrderAddress();
                    $userAddress
                        ->setZipCode($mgShippingAddress['postcode'])
                        ->setFullName($fullName)
                        ->setCountryCode($mgShippingAddress['country_id'])
                        ->setCity($mgShippingAddress['city'])
                        ->setAddress($mgShippingAddress['street'])
                        ->setMobilePhone($telephone)
                        ->setNationalId($this->getNationalId($mgShippingAddress, null))
                        ->setTaxId($this->getTaxId($mgShippingAddress, null))
                    ;
                    $orderShippingAddress = new PagantisModelOrderAddress();
                    $orderShippingAddress
                        ->setZipCode($mgShippingAddress['postcode'])
                        ->setFullName($fullName)
                        ->setCountryCode($mgShippingAddress['country_id'])
                        ->setCity($mgShippingAddress['city'])
                        ->setAddress($mgShippingAddress['street'])
                        ->setMobilePhone($telephone)
                    ;
                }
                if (isset($this->addressData[$i]) && array_search('billing', $this->addressData[$i])) {
                    $mgBillingAddress = $this->addressData[$i];
                    $orderBillingAddress = new PagantisModelOrderAddress();
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
            $orderUser = new PagantisModelOrderUser();

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
            ;

            $orderCollection = Mage::getModel('sales/order')->getCollection();
            $orderCollection = $orderCollection->addFieldToFilter('customer_id', $this->customer->getId());
            foreach ($orderCollection as $cOrder) {
                if ($cOrder->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                    $orderHistory = new PagantisModelOrderHistory();
                    $orderHistory
                        ->setAmount(floatval($cOrder->getGrandTotal())*100)
                        ->setDate($cOrder->getCreatedAt());
                    $orderUser->addOrderHistory($orderHistory);
                }
            }

            $promotedAmount = 0;
            $details = new PagantisModelOrderShoppingCartDetails();
            $details->setShippingCost(floatval($this->magentoOrder->getShippingAmount())*100);
            foreach ($this->itemCollection as $item) {
                $catalogProduct = Mage::getModel('catalog/product')->load($item->getId());
                $attributes = $catalogProduct->getAttributes();

                $product = new PagantisModelOrderShoppingCartProduct();
                $product
                    ->setAmount(floatval($item->getRowTotalInclTax())*100)
                    ->setQuantity($item->getQtyToShip())
                    ->setDescription($item->getName());
                $details->addProduct($product);

                $attributesobj = $attributes["pagantis_promoted"];
                $pagantisPromoted = $attributesobj->getFrontend()->getValue($catalogProduct) == "Si" ? 1 : 0;

                $productPrice = floatval($item->getRowTotalInclTax())*100;
                if ($pagantisPromoted) {
                    $metadata[$item->getId()] =
                        'Promoted Item: ' . $item->getName() .
                        ' Price: ' . floatval($item->getRowTotalInclTax())*100 .
                        ' Qty: ' . $item->getQtyToShip() .
                        ' Item ID: ' . $item->getId();
                    $promotedAmount += $productPrice;

                }
            }

            $orderShoppingCart = new PagantisModelOrderShoppingCart();
            $orderShoppingCart
                ->setDetails($details)
                ->setOrderReference($this->magentoOrderId)
                ->setPromotedAmount($promotedAmount)
                ->setTotalAmount((string) floor(100 * $this->magentoOrder->getGrandTotal()));

            $orderConfigurationUrls = new PagantisModelOrderUrls();
            $orderConfigurationUrls
                ->setOk($this->okUrl)
                ->setCancel($this->cancelUrl)
                ->setKo($this->okUrl)
                ->setAuthorizedNotificationCallback($this->okUrl)
                ->setRejectedNotificationCallback($this->okUrl);

            $orderChannel = new PagantisModelOrderChannel();
            $orderChannel
                ->setAssistedSale(false)
                ->setType(PagantisModelOrderChannel::ONLINE)
            ;

            $extraConfig = Mage::helper('pagantis/ExtraConfig')->getExtraConfig();
            $allowedCountries = unserialize($extraConfig['PAGANTIS_ALLOWED_COUNTRIES']);
            $langCountry = substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2);
            $shippingCountry = $mgShippingAddress['country_id'];
            $billingCountry = $mgBillingAddress['country_id'];
            $purchaseCountry =
                in_array($langCountry,$allowedCountries) ? $langCountry :
                in_array($shippingCountry,$allowedCountries) ? $shippingCountry :
                in_array($billingCountry,$allowedCountries) ? $billingCountry : null;

            $orderConfiguration = new PagantisModelOrderConfiguration();
            $orderConfiguration
                ->setChannel($orderChannel)
                ->setUrls($orderConfigurationUrls)
                ->setPurchaseCountry($purchaseCountry)
            ;

            $metadataOrder = new PagantisModelOrderMetadata();
            foreach ($metadata as $key => $metadatum) {
                $metadataOrder->addMetadata($key, $metadatum);
            }

            $order = new PagantisModelOrder();
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
            $orderClient = new PagantisClient(
                $this->publicKey,
                $this->privateKey
            );
            $order = $orderClient->createOrder($order);
            if ($order instanceof PagantisModelOrder) {
                $url = $order->getActionUrls()->getForm();
                $this->insertOrderControl($this->magentoOrderId, $order->getId());
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
                array('template' => 'pagantis/payment/iframe.phtml')
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
     * Create a record in AbstractController::PAGANTIS_ORDERS_TABLE to match the merchant order with the pagantis order
     *
     * @param $magentoOrderId
     * @param $pagantisOrderId
     *
     * @throws Exception
     */
    private function insertOrderControl($magentoOrderId, $pagantisOrderId)
    {
        $this->createTableIfNotExists('pagantis/order');
        $model = Mage::getModel('pagantis/order');
        $model->setData(array(
            'pagantis_order_id' => $pagantisOrderId,
            'mg_order_id' => $magentoOrderId,
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
}
