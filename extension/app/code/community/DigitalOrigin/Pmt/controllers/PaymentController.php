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
     * Index action
     */
    public function indexAction()
    {
        $salesOrder = Mage::getModel('sales/order');
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');
        /** @var integer $magentoOrderId */
        $magentoOrderId = $checkoutSession->getLastRealOrderId();
        /** @var Mage_Customer_Model_Session $customer */
        $customer = $customerSession->getCustomer();
        /** @var Mage_Sales_Model_Order $order */
        $magentoOrder = $salesOrder->loadByIncrementId($magentoOrderId);
        /** @var Mage_Core_Helper_Data $mageCore */
        $mageCore = Mage::helper('core');
        /** @var Mage_Sales_Model_Order_Item[] $itemCollection */
        $itemCollection = $magentoOrder->getAllVisibleItems();
        /** @var Mage_Sales_Model_Order $addressCollection */
        $addressCollection = $magentoOrder->getAddressesCollection();
        /** @var Array $moduleConfig */
        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        /** @var String $env */
        $env = $moduleConfig['PAYLATER_PROD'] ? 'PROD' : 'TEST';
        /** @var String $publicKey */
        $publicKey = $moduleConfig['PAYLATER_PUBLIC_KEY_' . $env];
        /** @var String $privateKey */
        $privateKey = $moduleConfig['PAYLATER_PRIVATE_KEY_' . $env];

        $magentoOrderData = json_decode($mageCore->jsonEncode($magentoOrder->getData()), true);
        $itemsData = array();

        $addressData = $addressCollection->getData();
        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        $okUrl = Mage::getUrl('pmt/notify', array('_query' => array('order' => $magentoOrderData['increment_id'])));
        $cancelUrl = Mage::getUrl('pmt/notify/cancel', array('_query' => array('order' => $magentoOrderData['increment_id'])));

        if ($magentoOrder->getStatus() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            return $this->_redirectUrl($callback);
        }


        foreach ($itemCollection as $item) {
            $itemsData[$item->getProductId()] = $item->getData();
        }

        $node = Mage::getConfig()->getNode();
        $metadata = array(
            'magento' => Mage::getVersion(),
            'pmt' => (string)$node->modules->DigitalOrigin_Pmt->version,
            'php' => phpversion(),
            'member_since' => $customer->getCreatedAt(),
        );

        $orderShippingAddress = null;
        try {
            for ($i = 0; $i <= count($addressData); $i++) {
                if (array_search('shipping', $addressData[$i])) {
                    $userAddress = new PmtModelOrderAddress();
                    $userAddress
                        ->setZipCode($addressData[$i]['postcode'])
                        ->setFullName($addressData[$i]['firstname'] . ' ' . $addressData[$i]['lastname'])
                        ->setCountryCode($addressData[$i]['country_id'])
                        ->setCity($addressData[$i]['city'])
                        ->setAddress($addressData[$i]['street']);
                    $orderShippingAddress = new PmtModelOrderAddress();
                    $orderShippingAddress
                        ->setZipCode($addressData[$i]['postcode'])
                        ->setFullName($addressData[$i]['firstname'] . ' ' . $addressData[$i]['lastname'])
                        ->setCountryCode($addressData[$i]['country_id'])
                        ->setCity($addressData[$i]['city'])
                        ->setAddress($addressData[$i]['street'])
                        ->setFixPhone($addressData[$i]['street'])
                        ->setMobilePhone($addressData[$i]['street']);
                }
                if (array_search('billing', $addressData[$i])) {
                    $orderBillingAddress = new PmtModelOrderAddress();
                    $orderBillingAddress
                        ->setZipCode($addressData[$i]['postcode'])
                        ->setFullName($addressData[$i]['firstname'] . ' ' . $addressData[$i]['lastname'])
                        ->setCountryCode($addressData[$i]['country_id'])
                        ->setCity($addressData[$i]['city'])
                        ->setAddress($addressData[$i]['street']);
                }
            }

            $orderUser = new PmtModelOrderUser();
            $fullName = null;
            $fixPhone = null;
            $mobilePhone = null;
            if ($orderShippingAddress) {
                $fullName = $orderShippingAddress->getFullName();
                $fixPhone = $orderShippingAddress->phone;
                $mobilePhone = $orderShippingAddress->phone_mobile;
            }
            if (!$fullName) {
                $fullName = $magentoOrderData['customer_firstname'] . ' ' . $magentoOrderData['customer_lastname'];
                $fixPhone = $shippingAddress->phone;
                $mobilePhone = $shippingAddress->phone_mobile;

            }
            $email = $customer->email ? $customer->email : $magentoOrderData['customer_email'];
            $orderUser
                ->setAddress($userAddress)
                ->setFullName($fullName)
                ->setBillingAddress($orderBillingAddress)
                ->setDateOfBirth($customer->birthday)
                ->setEmail($email)
                ->setFixPhone($fixPhone)
                ->setMobilePhone($mobilePhone)
                ->setShippingAddress($orderShippingAddress);


            $orderCollection = Mage::getModel('sales/order')->getCollection();
            $orderCollection = $orderCollection->addFieldToFilter('customer_id', $customer->getId());
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
            $details->setShippingCost(floatval($magentoOrder->getShippingAmount())*100);
            foreach ($itemCollection as $item) {
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
                ->setOrderReference($magentoOrderId)
                ->setPromotedAmount(0)
                ->setTotalAmount(floatval($magentoOrder->getGrandTotal())*100);

            $orderConfigurationUrls = new PmtModelOrderUrls();
            $orderConfigurationUrls
                ->setCancel($cancelUrl)
                ->setKo($cancelUrl)
                ->setNotificationCallback($okUrl)
                ->setOk($okUrl);

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
            $data = array();
            if ($magentoOrder) {
                $data['magentoOrder'] = (array) $magentoOrder;
            }
            if ($order) {
                $data['pmtOrder'] = (array) $order;
            }

            $this->saveLog($exception, $data);
            return $this->_redirectUrl($cancelUrl);
        }


        try {
            $orderClient = new PmtClient(
                $publicKey,
                $privateKey
            );

            $order = $orderClient->createOrder($order);
            if ($order instanceof PmtModelOrder) {
                $url = $order->getActionUrls()->getForm();
                $this->insertRow($magentoOrderId, $order->getId());
            } else {
                throw new \Exception('Order not created');
            }
        } catch (\Exception $exception) {
            $data = array();
            if ($order) {
                $data['pmtOrder'] = (array) $order;
            }

            $this->saveLog($exception, $data);
            return $this->_redirectUrl($cancelUrl);
        }

        if (!$moduleConfig['PAYLATER_IFRAME']) {
            try {
                return $this->_redirectUrl($url);
            } catch(\Exception $exception) {
                $data = array();
                if ($order) {
                    $data['pmtOrder'] = (array) $order;
                }

                $this->saveLog($exception, $data);
                return $this->_redirectUrl($cancelUrl);
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
            'checkoutUrl' => $cancelUrl,
            'leaveMessage' => $this->__('Are you sure you want to leave?')
        ));

        $this->getLayout()->getBlock('content')->append($block);
        return $this->renderLayout();
    }

    /**
     * @param $magentoOrderId
     * @param $pmtOrderId
     *
     * @return int
     */
    private function insertRow($magentoOrderId, $pmtOrderId)
    {
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = 'INSERT INTO ' . self::PMT_ORDERS_TABLE . ' VALUE (\'' . $magentoOrderId. '\', \'' . $pmtOrderId . '\')';

        $conn->query($sql);
    }
}
