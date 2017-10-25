<?php

class Aplazame_Aplazame_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    const METHOD_CODE = 'aplazame';
    const CHECKOUT_TOKEN = 'checkout_token';

    /**
     * options
     */
    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'aplazame/payment_form';
    protected $_infoBlockType = 'aplazame/payment_info';
    protected $_isInitializeNeeded     = true;
    protected $_canUseInternal         = false;
    protected $_canUseForMultishipping = false;


    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;

        $stateObject->setState($state);
        $stateObject->setStatus($state);
        $stateObject->setIsNotified(false);
    }

    private static function _orderTotal($order)
    {
        return $order->getTotalDue();
    }

    /**
     * Get aplazame session namespace
     */
    public function getSession()
    {
        return Mage::getSingleton('aplazame/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }


    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('aplazame/payment/redirect', array('_secure' => true));
    }

    public function getChargeId()
    {
        return $this->getInfoInstance()->getAdditionalInformation("charge_id");
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        if ($amount <= 0) {
            Mage::throwException(Mage::helper('aplazame')->__('Invalid amount for authorization.'));
        }

        $token = $payment->getAdditionalInformation(self::CHECKOUT_TOKEN);

        /** @var Aplazame_Aplazame_Model_Api_Client $api */
        $api = Mage::getModel('aplazame/api_client');

        $aOrder = $api->fetchOrder($token);
        if ($aOrder['total_amount'] !== Aplazame_Sdk_Serializer_Decimal::fromFloat($amount)->jsonSerialize() ||
            $aOrder['currency']['code'] !== Mage::app()->getStore()->getCurrentCurrencyCode()
        ) {
            Mage::throwException(
                Mage::helper('aplazame')->__(
                    'Aplazame authorized amount of ' . $aOrder['total_amount'] .
                    ' does not match requested amount of: ' . $amount
                )
            );
        }

        $result = $api->authorize($token);
        $this->getInfoInstance()->setAdditionalInformation("charge_id", $result["id"]);
        $payment->setTransactionId($this->getChargeId())->setIsTransactionClosed(0);
        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param mixed $checkout_token
     * @return $this
     */
    public function processConfirmOrder($order, $checkout_token)
    {
        $payment = $order->getPayment();

        $payment->setAdditionalInformation(self::CHECKOUT_TOKEN, $checkout_token);

        //authorize the total amount.
        $payment->authorize(true, self::_orderTotal($order));
        $payment->setAmountAuthorized(self::_orderTotal($order));


        if ((bool) $this->getConfigData('autoinvoice'))
        {
            //permitimos capturar en este caso, sino fallaria la generacion de factura
            $this->_canCapture = true;

            $invoice = $order->prepareInvoice();
            if ($invoice->getGrandTotal() > 0) { // Evitamos captar un pago con total cero.
                $invoice
                    ->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE)
                    ->register()
                    ->capture();
                $order->addRelatedObject($invoice);
                $payment->setCreatedInvoice($invoice);
                Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
            }
        }

        $order->save();

        return $this;
    }

    public function getCheckoutSerializer()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        return Aplazame_Aplazame_BusinessModel_Checkout::createFromOrder($order);
    }
}
