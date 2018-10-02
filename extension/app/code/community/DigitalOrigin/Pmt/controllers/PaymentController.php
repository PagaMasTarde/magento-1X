<?php

require_once('lib/DigitalOrigin/autoload.php');
require_once('app/code/community/DigitalOrigin/Pmt/controllers/AbstractController.php');

use PagaMasTarde\OrdersApiClient\Model\Order as PmtModelOrder;
use PagaMasTarde\OrdersApiClient\Model\Order\User as PmtModelOrderUser;
use PagaMasTarde\OrdersApiClient\Model\Order\User\Address as PmtModelOrderAddress;
use PagaMasTarde\OrdersApiClient\Model\Order\User\OrderHistory as PmtModelOrderHistory;
use PagaMasTarde\OrdersApiClient\Model\Order\Metadata as PmtModelOrderMetadata;
use PagaMasTarde\OrdersApiClient\Model\Order\ShoppingCart as PmtModelOrderShoppingCart;
use PagaMasTarde\OrdersApiClient\Model\Order\ShoppingCart\Details as PmtModelOrderShoppingCartDetails;
use PagaMasTarde\OrdersApiClient\Model\Order\ShoppingCart\Details\Product as PmtModelOrderShoppingCartProduct;
use PagaMasTarde\OrdersApiClient\Model\Order\Configuration as PmtModelOrderConfiguration;
use PagaMasTarde\OrdersApiClient\Model\Order\Configuration\Urls as PmtModelOrderUrls;
use PagaMasTarde\OrdersApiClient\Model\Order\Configuration\Channel as PmtModelOrderChannel;
use PagaMasTarde\OrdersApiClient\Client as PmtClient;
use PagaMasTarde\OrdersApiClient\Exception\ClientException as PmtClientException;

/**
 * Class DigitalOrigin_Pmt_PaymentController
 */
class DigitalOrigin_Pmt_PaymentController extends AbstractController
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
     * @var boolean $iframe
     */
    protected $iframe;

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

        $this->okUrl = Mage::getUrl('pmt/notify', array('_query' => array(
                'order' => $this->magentoOrderData['increment_id']))
        );
        $this->cancelUrl = Mage::getUrl('pmt/notify/cancel', array('_query' => array(
                'order' => $this->magentoOrderData['increment_id']))
        );

        $this->itemCollection = $this->magentoOrder->getAllVisibleItems();
        $addressCollection = $this->magentoOrder->getAddressesCollection();
        $this->addressData = $addressCollection->getData();

        $customerSession = Mage::getSingleton('customer/session');
        $this->customer = $customerSession->getCustomer();

        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        $env = $moduleConfig['PAYLATER_PROD'] ? 'PROD' : 'TEST';
        $this->publicKey = $moduleConfig['PAYLATER_PUBLIC_KEY_' . $env];
        $this->privateKey = $moduleConfig['PAYLATER_PRIVATE_KEY_' . $env];
        $this->iframe = $moduleConfig['PAYLATER_IFRAME'];
    }

    /**
     * Default Action controller, launch in a new purchase.
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
            'pmt' => (string)$node->modules->DigitalOrigin_Pmt->version,
            'php' => phpversion(),
            'member_since' => $this->customer->getCreatedAt(),
        );

        $fullName = null;
        $telephone = null;
        $userAddress = null;
        $orderShippingAddress = null;
        $orderBillingAddress = null;
        try {
            for ($i = 0; $i <= count($this->addressData); $i++) {
                if (array_search('shipping', $this->addressData[$i])) {
                    $fullName = $this->addressData[$i]['firstname'] . ' ' . $this->addressData[$i]['lastname'];
                    $telephone = $this->addressData[$i]['telephone'];
                    $userAddress = new PmtModelOrderAddress();
                    $userAddress
                        ->setZipCode($this->addressData[$i]['postcode'])
                        ->setFullName($fullName)
                        ->setCountryCode($this->addressData[$i]['country_id'])
                        ->setCity($this->addressData[$i]['city'])
                        ->setAddress($this->addressData[$i]['street'])
                        ->setMobilePhone($telephone);
                    $orderShippingAddress = new PmtModelOrderAddress();
                    $orderShippingAddress
                        ->setZipCode($this->addressData[$i]['postcode'])
                        ->setFullName($fullName)
                        ->setCountryCode($this->addressData[$i]['country_id'])
                        ->setCity($this->addressData[$i]['city'])
                        ->setAddress($this->addressData[$i]['street'])
                        ->setMobilePhone($telephone);
                }
                if (array_search('billing', $this->addressData[$i])) {
                    $orderBillingAddress = new PmtModelOrderAddress();
                    $orderBillingAddress
                        ->setZipCode($this->addressData[$i]['postcode'])
                        ->setFullName($this->addressData[$i]['firstname'] . ' ' . $this->addressData[$i]['lastname'])
                        ->setCountryCode($this->addressData[$i]['country_id'])
                        ->setCity($this->addressData[$i]['city'])
                        ->setAddress($this->addressData[$i]['street'])
                        ->setMobilePhone($this->addressData[$i]['telephone']);
                }
            }

            if (is_null($fullName)) {
                $fullName = $this->magentoOrderData['customer_firstname'] . ' ' . $this->magentoOrderData['customer_lastname'];
            }

            if (is_null($telephone)) {
                $addr =  $this->customer->getPrimaryShippingAddress();
                $telephone = $addr->getTelephone();
            }
            $email = $this->customer->email ? $this->customer->email : $this->magentoOrderData['customer_email'];
            $orderUser = new PmtModelOrderUser();

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
                ->setDateOfBirth($this->customer->birthday)
                ->setEmail($email)
                ->setMobilePhone($telephone)
                ->setAddress($userAddress)
                ->setShippingAddress($orderShippingAddress)
                ->setBillingAddress($orderBillingAddress);


            $orderCollection = Mage::getModel('sales/order')->getCollection();
            $orderCollection = $orderCollection->addFieldToFilter('customer_id', $this->customer->getId());
            foreach ($orderCollection as $cOrder) {
                if ($cOrder->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                    $orderHistory = new PmtModelOrderHistory();
                    $orderHistory
                        ->setAmount(floatval($cOrder->getGrandTotal())*100)
                        ->setDate((string)$cOrder->getCreatedAtFormated()->getDate());
                    $orderUser->addOrderHistory($orderHistory);
                }
            }

            $details = new PmtModelOrderShoppingCartDetails();
            $details->setShippingCost(floatval($this->magentoOrder->getShippingAmount())*100);
            foreach ($this->itemCollection as $item) {
                $product = new PmtModelOrderShoppingCartProduct();
                $product
                    ->setAmount(floatval($item->getRowTotalInclTax())*100)
                    ->setQuantity($item->getQtyToShip())
                    ->setDescription($item->getName());
                $details->addProduct($product);
            }

            $orderShoppingCart = new PmtModelOrderShoppingCart();
            $orderShoppingCart
                ->setDetails($details)
                ->setOrderReference($this->magentoOrderId)
                ->setPromotedAmount(0)
                ->setTotalAmount(floatval($this->magentoOrder->getGrandTotal())*100);

            $orderConfigurationUrls = new PmtModelOrderUrls();
            $orderConfigurationUrls
                ->setCancel($this->cancelUrl)
                ->setKo($this->cancelUrl)
                ->setNotificationCallback($this->okUrl)
                ->setOk($this->okUrl);

            $orderChannel = new PmtModelOrderChannel();
            $orderChannel
                ->setAssistedSale(false)
                ->setType(PmtModelOrderChannel::ONLINE)
            ;

            $orderConfiguration = new PmtModelOrderConfiguration();
            $orderConfiguration
                ->setChannel($orderChannel)
                ->setUrls($orderConfigurationUrls)
            ;

            $metadataOrder = new PmtModelOrderMetadata();
            foreach ($metadata as $key => $metadatum) {
                $metadataOrder->addMetadata($key, $metadatum);
            }

            $order = new PmtModelOrder();
            $order
                ->setConfiguration($orderConfiguration)
                ->setMetadata($metadataOrder)
                ->setShoppingCart($orderShoppingCart)
                ->setUser($orderUser);
        } catch (\Exception $exception) {
            $this->logException($exception);
            return $this->_redirectUrl($this->cancelUrl);
        }


        try {
            $orderClient = new PmtClient(
                $this->publicKey,
                $this->privateKey
            );
            $order = $orderClient->createOrder($order);
            if ($order instanceof PmtModelOrder) {
                $url = $order->getActionUrls()->getForm();
                $this->insertRow($this->magentoOrderId, $order->getId());
            } else {
                throw new \Exception('Order not created');
            }
        } catch (\Exception $exception) {
            $this->order = $order;
            $this->logException($exception);
            return $this->_redirectUrl($this->cancelUrl);
        }

        if (!$this->iframe) {
            try {
                return $this->_redirectUrl($url);
            } catch(\Exception $exception) {
                $this->logException($exception);
                return $this->_redirectUrl($this->cancelUrl);
            }
        }
        $this->loadLayout();

        /** @var Mage_Core_Block_Template $block */
        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'custompaymentmethod',
            array('template' => 'pmt/payment/iframe.phtml')
        );


        $block->assign(array(
            'orderUrl' => $url,
            'checkoutUrl' => $this->cancelUrl,
            'leaveMessage' => $this->__('Are you sure you want to leave?')
        ));

        $this->getLayout()->getBlock('content')->append($block);
        return $this->renderLayout();
    }

    public function logException(\Exception $exception) {
        $data = array();
        $debug = $exception->getTrace();
        $method = $debug[0]['function'];
        $line = $debug[0]['line'];
        $class = $debug[0]['class'];
        $data['message'] = $exception->getMessage();
        $data['code'] = $exception->getCode();
        $data['method'] = $method;
        $data['line'] = $line;
        $data['class'] =str_replace("\\", "\\\\",  $class);
        $this->saveLog($data);
    }

    /**
     * @param $magentoOrderId
     * @param $pmtOrderId
     *
     * @throws Exception
     */
    private function insertRow($magentoOrderId, $pmtOrderId)
    {
        $model = Mage::getModel('pmt/order');
        $model->setData(array(
            'pmt_order_id' => $pmtOrderId,
            'mg_order_id' => $magentoOrderId,
        ));
        $model->save();
    }
}
