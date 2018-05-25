<?php

/**
 * Class DigitalOrigin_Pmt_Model_Paylater
 */
class DigitalOrigin_Pmt_Model_Paylater extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var string
     */
    protected $_code  = 'paylater';

    /**
     * @var string
     */
    protected $_formBlockType = 'pmt/checkout_paylater';

    /**
     * @var bool
     */
    protected $_isInitializeNeeded      = true;

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $paymentDetailArray = Mage::app()->getRequest()->getPost('paymentdetail');
        $paymentDetail = $paymentDetailArray[0];
        $this->getCheckout()->setPaymentMethodDetail($paymentDetail);
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    /**
     * @param mixed $data
     *
     * @return $this
     */
    public function assignData($data)
    {
        $this->getInfoInstance();

        return $this;
    }

    /**
     * @return $this
     */
    public function validate()
    {
        parent::validate();

        $this->getInfoInstance();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('pmt/payment', array('_secure' => false));
    }

    /**
     * @param Mage_Sales_Model_Quote $quote = null
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if ($this->getConfigData('enabled') == 'no') {
            return false;
        }

        $min = $this->getConfigData('MIN_AMOUNT');

        if ($quote && $quote->getBaseGrandTotal() < $min) {
            return false;
        }

        $env = $this->getConfigData('PAYLATER_PROD') ? 'PROD' : 'TEST';
        $publicKey = $this->getConfigData('PAYLATER_PUBLIC_KEY_'.$env);
        $privateKey = $this->getConfigData('PAYLATER_PRIVATE_KEY_'.$env);

        if (!$publicKey || !$privateKey) {
            return false;
        }

        return parent::isAvailable();
    }
}
