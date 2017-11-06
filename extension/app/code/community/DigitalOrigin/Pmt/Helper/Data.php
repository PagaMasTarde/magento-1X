<?php

/**
 * Class DigitalOrigin_Pmt_Helper_Data
 */
class DigitalOrigin_Pmt_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/paylater/active');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    private function _processOrder(Mage_Sales_Model_Order $order)
    {
        try {
            if ($order->getId()) {
                if ($order->canInvoice()) {
                    /** @var Mage_Sales_Model_Order_Invoice $invoice */
                    $invoice = $this->_createInvoice($order);
                    $comment = Mage::helper('DigitalOrigin_Ptm')->__(
                        'Transaction authorised. Billing created %s',
                        $invoice->getIncrementId()
                    );
                } else {
                    $comment = Mage::helper('DigitalOrigin_Ptm')->__('Transaction denied, billing not created');
                }

                $order->setState(
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    $comment,
                    true
                );
                $order->sendNewOrderEmail();
                $order->save();

            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
