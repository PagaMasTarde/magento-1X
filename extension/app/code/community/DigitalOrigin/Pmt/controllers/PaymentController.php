<?php

class Aplazame_Aplazame_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getAccessToken()
    {
        $request = new Zend_Controller_Request_Http();
        $authorization = $request->getHeader('authorization');

        if ($authorization) {
            $token = explode(' ', $authorization);
            $token =$token[1];
            if (isset($token) && is_string($token)) {
                return $token;
            }

            Mage::throwException($this->__('Authentication header format is invalid.'));
        }

        Mage::throwException($this->__('Authentication header is absent.'));
    }

    public function redirectAction()
    {
        $session = $this->_getCheckoutSession();

        if (!$session->getLastRealOrderId()) {
            $session->addError($this->__('Your order has expired.'));
            $this->_redirect('checkout/cart');
            return;
        }

        $this->getResponse()->setBody($this->getLayout()->createBlock('aplazame/payment_redirect')->toHtml());

        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }


    public function confirmAction()
    {
        $session = $this->_getCheckoutSession();
        $checkout_token = $this->getRequest()->getParam("order_id");

        if (!$checkout_token) {
            Mage::throwException($this->__('Confirm has no checkout token.'));
        }

        if (!$session->getLastRealOrderId()) {
            Mage::throwException($this->__('Session has expired.'));
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
        $payment = $order->getPayment()->getMethodInstance();
        if (!$payment instanceof Aplazame_Aplazame_Model_Payment) {
            Mage::throwException($this->__('Unexpected payment method.'));
        }

        $payment->processConfirmOrder($order, $checkout_token);

        $order->sendNewOrderEmail();
    }

    public function cancelAction()
    {
        $this->_redirectUrl(Mage::getUrl('aplazame/payment/cart'));
    }

    public function cartAction()
    {
        $session = $this->_getCheckoutSession();
        $orderId = $session->getLastRealOrderId();

        if ($orderId) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if ($order->getId() && $order->getState() === Mage_Sales_Model_Order::STATE_NEW) {
                /** @var Aplazame_Aplazame_Helper_Cart $cart */
                $cart = Mage::helper('aplazame/cart');
                $cart->resuscitateCartFromOrder($order, $this);
            }
        }

        $this->_redirectUrl(Mage::helper('checkout/url')->getCheckoutUrl());
    }

    public function historyAction()
    {
        $checkout_token = $this->getRequest()->getParam("checkout_token");

        if (!$checkout_token) {
            Mage::throwException($this->__('History has no checkout token.'));
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($checkout_token);
        $payment = $order->getPayment()->getMethodInstance();
        if (!$payment instanceof Aplazame_Aplazame_Model_Payment) {
            Mage::throwException($this->__('Unexpected payment method.'));
        }

        $code = Aplazame_Aplazame_Model_Payment::METHOD_CODE;

        if (!$payment or $code !== $payment->getCode()) {
            Mage::throwException($this->__('Order not found.'));
        }

        if ($this->_getAccessToken() !== $payment->getConfigData('secret_api_key')) {
            Mage::throwException($this->__('You don\'t have permissions.'));
        }

        /** @var Mage_Sales_Model_Order[] $history_collection */
        $history_collection = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('customer_id', array('like'=> $order->getCustomerId()));

        $historyOrders = array_map(array('Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder', 'createFromOrder'), $history_collection);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($historyOrders)));
    }
}
