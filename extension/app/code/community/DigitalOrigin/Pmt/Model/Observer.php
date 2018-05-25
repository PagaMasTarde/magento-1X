<?php

/**
 * Class DigitalOrigin_Pmt_Model_Observer
 */
class DigitalOrigin_Pmt_Model_Observer
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
                ->addFieldToFilter('method', 'paylater')
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
                        $history = $order->addStatusHistoryComment('Order Expired in PMT', false);
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
}
