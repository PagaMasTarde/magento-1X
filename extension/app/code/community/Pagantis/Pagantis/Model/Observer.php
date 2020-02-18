<?php

/**
 * Class Pagantis_Pagantis_Model_Observer
 */
class Pagantis_Pagantis_Model_Observer
{
    /**
     * Cancel Orders after Expiration
     */
    public function cancelPendingOrders()
    {
        try {
            $orders = Mage::getModel('sales/order')->getCollection();
            $orders->getSelect()->join(
                array('p' => $orders->getResource()->getTable('sales/order_payment')),
                'p.parent_id = main_table.entity_id',
                array()
            );
            $orders
                ->addFieldToFilter('status', 'pending_payment')
                ->addFieldToFilter('method', 'pagantis')
                ->addFieldToFilter('created_at', array(
                    'from'     => strtotime('-7 days', time()),
                    'to'       => strtotime('-60 minutes', time()),
                    'datetime' => true
                ))
            ;

            foreach ($orders as $order) {
                if ($order->canCancel()) {
                    try {
                        $order->cancel();
                        $order->getStatusHistoryCollection(true);
                        $history = $order->addStatusHistoryComment('Order Expired in Pagantis', false);
                        $history->setIsCustomerNotified(false);
                        $order->save();
                    } catch (\Exception $exception) {
                        Mage::logException($exception);
                    }
                }
            }
        } catch (\Exception $exception) {
            Mage::logException($exception);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
        $method = $observer->getMethodInstance();
        if ($method->getCode() == 'pagantis') {
            $extraConfig      = Mage::helper('pagantis/ExtraConfig')->getExtraConfig();
            $locale           = substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2);
            $allowedCountries = unserialize($extraConfig['PAGANTIS_ALLOWED_COUNTRIES']);
            $minAmount        = $extraConfig['PAGANTIS_DISPLAY_MIN_AMOUNT'];
            $maxAmount        = $extraConfig['PAGANTIS_DISPLAY_MAX_AMOUNT'];
            $checkoutSession  = Mage::getModel('checkout/session');
            $quote = $checkoutSession->getQuote();
            $amount = $quote->getGrandTotal();
            if (!in_array(strtolower($locale), $allowedCountries) ||
                (int)$amount < (int)$minAmount ||
                ((int)$amount > (int)$maxAmount && (int)$maxAmount !== 0)) {
                $result = $observer->getResult();
                $result->isAvailable = false;
            }
        }
    }
}
