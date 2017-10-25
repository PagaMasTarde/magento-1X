<?php

class Aplazame_Aplazame_Helper_Cart extends Mage_Core_Helper_Abstract
{
    /**
     * Funcion que resucita un carrito (quote) en caso
     * de producirse algún fallo en el proceso de cobro de aplazame
     * o bien se ha rechazado la operación.
     */
    public function resuscitateCartFromOrder(Mage_Sales_Model_Order $order, Mage_Core_Controller_Front_Action $action = null)
    {
        $this
            ->_cancelOrder($order)
            ->_resuscitateCartItems($order, $action)
            ->_setCheckoutInfoFromOldOrder($order);

        return $this;
    }

    /**
     * Cancela la order anterior que se le pasa como parametro
     *
     * @param Mage_Sales_Model_Order $order
     * @return $this
     */
    protected function _cancelOrder(Mage_Sales_Model_Order $order)
    {
        $order
            ->cancel()
            ->save();

        return $this;
    }


    /**
     * Re-añade los productos comprados a carrito nuevamente
     *
     * @param Mage_Sales_Model_Order $order
     * @return $this
     */
    protected function _resuscitateCartItems(Mage_Sales_Model_Order $order, Mage_Core_Controller_Front_Action $action=null)
    {
        foreach ($order->getItemsCollection() as $orderItem) {
            try {
                $this->getCart()->addOrderItem($orderItem);
            } catch (Mage_Core_Exception $e) {
                /** @var Mage_Checkout_Model_Session $session */
                $session = Mage::getSingleton('checkout/session');
                if ($session->getUseNotice(true)) {
                    $session->addNotice($e->getMessage());
                } else {
                    $session->addError($e->getMessage());
                }

                if($action)
                {
                    $action->setRedirectWithCookieCheck('checkout/cart');
                }
            } catch (Exception $e) {
                /** @var Mage_Checkout_Model_Session $session */
                $session = Mage::getSingleton('checkout/session');
                $session->addNotice($e->getMessage());
                $session->addException($e, Mage::helper('checkout')->__('Cannot add the item to shopping cart.'));
                if($action)
                {
                    $action->setRedirectWithCookieCheck('checkout/cart');
                }
            }
        }

        $this->getCart()->save();
        return $this;
    }

    /**
     * Coge las billing y shipping address de la ultima order
     * el checkout method y shipping method
     * y las vuelve a meter en quote actual para que el checkout
     * pueda tener las direcciones pre-populadas.
     *
     * @param Mage_Sales_Model_Order $order
     */
    protected function _setCheckoutInfoFromOldOrder(Mage_Sales_Model_Order $order)
    {
        $checkoutSession = $this->getCheckoutSession();
        $quote = $checkoutSession->getQuote();
        /** @var Mage_Sales_Model_Quote $oldQuote */
        $oldQuote = Mage::getModel('sales/quote')->load($order->getQuoteId());

        $quote->setCheckoutMethod($oldQuote->getCheckoutMethod());

        if ($oldQuote->getId()) {
            $billingAddress = clone $oldQuote->getBillingAddress();
            $billingAddress->setQuote($quote);

            $shippingAddress = clone $oldQuote->getShippingAddress();
            $shippingAddress->setQuote($quote);

            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->removeAddress($quote->getShippingAddress()->getId());

            $quote->setBillingAddress($billingAddress);
            $quote->setShippingAddress($shippingAddress);
        }

        $quote->save();

        return $this;
    }

    /**
     * @return Mage_Checkout_Model_Cart
     */
    public function getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}
