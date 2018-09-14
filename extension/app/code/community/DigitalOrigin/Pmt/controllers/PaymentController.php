<?php

require_once('lib/DigitalOrigin/autoload.php');
require_once('app/code/community/DigitalOrigin/Pmt/controllers/BaseController.php');

/**
 * Class DigitalOrigin_Pmt_PaymentController
 */
class DigitalOrigin_Pmt_PaymentController extends BaseController
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
        $orderId = $checkoutSession->getLastRealOrderId();
        /** @var Mage_Customer_Model_Session $customer */
        $customer = $customerSession->getCustomer();
        /** @var Mage_Sales_Model_Order $order */
        $magentoOrder = $salesOrder->loadByIncrementId($orderId);
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
        /** @var Mage_Customer_Model_Customer $customer */
        $metadata = array(
            'magento' => Mage::getVersion(),
            'pmt' => (string)$node->modules->DigitalOrigin_Pmt->version,
            'php' => phpversion(),
            'member_since' => $customer->getCreatedAt(),
        );

        try {
            for ($i = 0; $i <= count($addressData); $i++) {
                if (array_search('shipping', $addressData[$i])) {
                    $userAddress = new \PagaMasTarde\OrdersApiClient\Model\Order\User\Address();
                    $userAddress
                        ->setZipCode($addressData[$i]['postcode'])
                        ->setFullName($addressData[$i]['firstname'] . ' ' . $addressData[$i]['lastname'])
                        ->setCountryCode($addressData[$i]['country_id'])
                        ->setCity($addressData[$i]['city'])
                        ->setAddress($addressData[$i]['street']);
                    $orderShippingAddress = new \PagaMasTarde\OrdersApiClient\Model\Order\User\Address();
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
                    $orderBillingAddress = new \PagaMasTarde\OrdersApiClient\Model\Order\User\Address();
                    $orderBillingAddress
                        ->setZipCode($addressData[$i]['postcode'])
                        ->setFullName($addressData[$i]['firstname'] . ' ' . $addressData[$i]['lastname'])
                        ->setCountryCode($addressData[$i]['country_id'])
                        ->setCity($addressData[$i]['city'])
                        ->setAddress($addressData[$i]['street']);
                }
            }

            $orderUser = new \PagaMasTarde\OrdersApiClient\Model\Order\User();
            $orderUser
                ->setAddress($userAddress)
                ->setFullName($orderShippingAddress->getFullName())
                ->setBillingAddress($orderBillingAddress)
                ->setDateOfBirth($customer->birthday)
                ->setEmail($this->context->cookie->logged ? $this->context->cookie->email : $customer->email)
                ->setFixPhone($shippingAddress->phone)
                ->setMobilePhone($shippingAddress->phone_mobile)
                ->setShippingAddress($orderShippingAddress);


            $orderCollection = Mage::getModel('sales/order')->getCollection();
            $orderCollection = $orderCollection->addFieldToFilter('customer_id', $customer->getId());
            foreach ($orderCollection as $cOrder) {
                if ($cOrder->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                    $orderHistory = new \PagaMasTarde\OrdersApiClient\Model\Order\User\OrderHistory();
                    $orderHistory
                        ->setAmount(floatval($cOrder->getGrandTotal())*100)
                        ->setDate((string)$cOrder->getCreatedAtFormated()->getDate());
                    $orderUser->addOrderHistory($orderHistory);
                }
            }

            $details = new \PagaMasTarde\OrdersApiClient\Model\Order\ShoppingCart\Details();
            $details->setShippingCost(floatval($magentoOrder->getShippingAmount())*100);
            foreach ($itemCollection as $item) {
                $product = new \PagaMasTarde\OrdersApiClient\Model\Order\ShoppingCart\Details\Product();
                $product
                    ->setAmount(floatval($item->getRowTotalInclTax())*100)
                    ->setQuantity($item->getQtyToShip())
                    ->setDescription($item->getName());
                $details->addProduct($product);
            }

            $orderShoppingCart = new \PagaMasTarde\OrdersApiClient\Model\Order\ShoppingCart();
            $orderShoppingCart
                ->setDetails($details)
                ->setOrderReference($orderId)
                ->setPromotedAmount(0)
                ->setTotalAmount(floatval($magentoOrder->getGrandTotal())*100);

            $orderConfigurationUrls = new \PagaMasTarde\OrdersApiClient\Model\Order\Configuration\Urls();
            $orderConfigurationUrls
                ->setCancel($cancelUrl)
                ->setKo($cancelUrl)
                ->setNotificationCallback($okUrl)
                ->setOk($okUrl);

            $orderChannel = new \PagaMasTarde\OrdersApiClient\Model\Order\Configuration\Channel();
            $orderChannel
                ->setAssistedSale(false)
                ->setType(\PagaMasTarde\OrdersApiClient\Model\Order\Configuration\Channel::ONLINE)
            ;

            $orderConfiguration = new \PagaMasTarde\OrdersApiClient\Model\Order\Configuration();
            $orderConfiguration
                ->setChannel($orderChannel)
                ->setUrls($orderConfigurationUrls)
            ;

            $metadataOrder = new \PagaMasTarde\OrdersApiClient\Model\Order\Metadata();
            foreach ($metadata as $key => $metadatum) {
                $metadataOrder->addMetadata($key, $metadatum);
            }

            $order = new \PagaMasTarde\OrdersApiClient\Model\Order();
            $order
                ->setConfiguration($orderConfiguration)
                ->setMetadata($metadataOrder)
                ->setShoppingCart($orderShoppingCart)
                ->setUser($orderUser);
        } catch (\PagaMasTarde\OrdersApiClient\Exception\ValidationException $validationException) {
            Mage::log(
                json_encode(array(
                    'code' => $validationException->getCode(),
                    'exception' => $validationException->getMessage(),
                    'magentoOrder' => json_encode($magentoOrder),
                    'order' => json_encode($order),
                    'timestamp' => time()
                )),
                null,
                'pmt.log',
                true
            );
            return $this->_redirectUrl($cancelUrl);
        }


        try {
            $orderClient = new \PagaMasTarde\OrdersApiClient\Client(
                $publicKey,
                $privateKey
            );
            $order = $orderClient->createOrder($order);
            if ($order instanceof \PagaMasTarde\OrdersApiClient\Model\Order) {
                $url = $order->getActionUrls()->getForm();
            } else {
                throw new \Exception('Order not created');
            }
        } catch (\Exception $exception) {
            Mage::log(
                json_encode(array(
                    'code' => $validationException->getCode(),
                    'exception' => $validationException->getMessage(),
                    'order' => json_encode($order),
                    'timestamp' => time()
                )),
                null,
                'pmt.log',
                true
            );
            return $this->_redirectUrl($cancelUrl);
        }

        if (!$moduleConfig['PAYLATER_IFRAME']) {
            try {
                return $this->_redirectUrl($url);
            } catch(\Exception $exception) {
                Mage::log(
                    json_encode(array(
                        'code' => $exception->getCode(),
                        'exception' => $exception->getMessage(),
                        'order' => json_encode($order),
                        'timestamp' => time()
                    )),
                    null,
                    'pmt.log',
                    true
                );
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
}
