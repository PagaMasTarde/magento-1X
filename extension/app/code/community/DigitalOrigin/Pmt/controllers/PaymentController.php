<?php

require_once('lib/DigitalOrigin/autoload.php');

/**
 * Class DigitalOrigin_Pmt_PaymentController
 */
class DigitalOrigin_Pmt_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Shopper Url
     */
    const SHOPPER_URL = 'https://shopper.pagamastarde.com/magento/';

    /**
     * CSS URL
     */
    const CSS_URL = 'https://shopper.pagamastarde.com/css/paylater-modal.min.css';

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
        $order = $salesOrder->loadByIncrementId($orderId);
        /** @var Mage_Core_Helper_Data $mageCore */
        $mageCore = Mage::helper('core');

        if ($order->getStatus() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            return $this->_redirect('checkout/cart');
        }

        /** @var Mage_Sales_Model_Order  $itemCollection */
        $itemCollection = $order->getItemsCollection();
        /** @var Mage_Sales_Model_Order  $addressCollection */
        $addressCollection = $order->getAddressesCollection();

        $orderData = json_decode($mageCore->jsonEncode($order->getData()), true);
        $customerData = json_decode($mageCore->jsonEncode($customer->getData()), true);
        $itemsData = json_decode($mageCore->jsonEncode($itemCollection->getData()), true);
        //TODO workout send category to shopper
        //TODO CREATE CATEGORY
        //TODO TEST PPP
        $addressData = json_decode($mageCore->jsonEncode($addressCollection->getData()), true);
        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        $back = Mage::getUrl('pmt/notify', array('_query' => array('order' => $orderData['entity_id'])));
        $backCancel = Mage::getUrl('pmt/notify/cancel', array('_query' => array('order' => $orderData['entity_id'])));

        $url = array(
            'ok' => $back,
            'ko' => $backCancel,
            'callback' => $back,
            'cancelled' => $backCancel,
        );

        $node = Mage::getConfig()->getNode();

        /** @var Mage_Customer_Model_Customer $customer*/
        $metadata = array(
            'magento' => Mage::getVersion(),
            'pmt' => (string) $node->modules->DigitalOrigin_Pmt->version,
            'php' => phpversion(),
            'member_since' => $customer->getCreatedAtTimestamp(),
        );

        $magentoObjectModule = new \ShopperLibrary\ObjectModule\MagentoObjectModule();
        $magentoObjectModule
            ->setOrder($orderData)
            ->setCustomer($customerData)
            ->setItems($itemsData)
            ->setAddress($addressData)
            ->setModule($moduleConfig)
            ->setUrl($url)
            ->setMetadata($metadata)
        ;

        $shopperClient = new \ShopperLibrary\ShopperClient(self::SHOPPER_URL);
        $shopperClient->setObjectModule($magentoObjectModule);
        $paymentForm = $shopperClient->getPaymentForm();
        $shopperResponse = json_decode($paymentForm);
        $url = $shopperResponse->data->url;

        //Redirect
        if (!$moduleConfig['PAYLATER_IFRAME']) {
            return $this->_redirectUrl($url);
        }

        //iframe
        $this->loadLayout();

        /** @var Mage_Core_Block_Template $block */
        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'custompaymentmethod',
            array('template' => 'pmt/payment/iframe.phtml')
        );

        $block->assign(array(
            'url' => $url,
            'checkoutUrl' => $backCancel,
            'css' => self::CSS_URL,
        ));

        $this->getLayout()->getBlock('content')->append($block);
        return $this->renderLayout();
    }
}
