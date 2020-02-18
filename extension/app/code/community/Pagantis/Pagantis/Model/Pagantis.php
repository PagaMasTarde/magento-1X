<?php

/**
 * Class Pagantis_Pagantis_Model_Pagantis
 */
class Pagantis_Pagantis_Model_Pagantis extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var string
     */
    protected $_code  = 'pagantis';

    /**
     * @var string
     */
    protected $_formBlockType = 'pagantis/checkout_pagantis';

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
        return Mage::getUrl('pagantis/payment', array('_secure' => false));
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

        $extraConfig    = Mage::helper('pagantis/ExtraConfig')->getExtraConfig();
        $minAmount        = $extraConfig['PAGANTIS_DISPLAY_MIN_AMOUNT'];
        $maxAmount        = $extraConfig['PAGANTIS_DISPLAY_MAX_AMOUNT'];

        if ($quote && floor($quote->getBaseGrandTotal()) < $minAmount) {
            return false;
        }

        if ($quote && floor($quote->getBaseGrandTotal()) > $maxAmount && $maxAmount != '0') {
            return false;
        }

        $publicKey = $this->getConfigData('pagantis_public_key');
        $privateKey = $this->getConfigData('pagantis_public_key');

        if (!$publicKey || !$privateKey) {
            return false;
        }

        return parent::isAvailable();
    }
}
