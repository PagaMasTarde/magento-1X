<?php

/**
 * Class DigitalOrigin_Pmt_NotifyController
 */
class DigitalOrigin_Pmt_NotifyController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     */
    const CODE = 'paylater';

    /**
     * @var string $message
     */
    protected $message;
    /**
     * @var bool $error
     */
    protected $error = false;

    /**
     * Cancel order
     *
     * @var bool
     */
    protected $toCancel = false;

    /**
     * Cancel action
     */
    public function cancelAction()
    {
        $this->toCancel = true;

        return $this->indexAction();
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $orderId = Mage::app()->getRequest()->getParam('order');
        $order = Mage::getModel('sales/order')->load($orderId);
        $status = $order->getStatus();
        $payment = $order->getPayment();
        $code = $payment->getMethodInstance()->getCode();

        if ($status == Mage_Sales_Model_Order::STATE_PROCESSING ||
            $status == Mage_Sales_Model_Order::STATE_COMPLETE ||
            $code != self::CODE
        ) {
            return $this->_redirect('sales/order/view/', array('order_id' => $order->getId()));
        }

        try {
            $this->processValidation();
        } catch (\Exception $exception) {
            $this->message = $exception->getMessage();
            $this->error = true;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return $this->jsonResponse();
        } else {
            return $this->redirect();
        }
    }

    /**
     * Send a jsonResponse
     */
    public function jsonResponse()
    {
        $orderId = Mage::app()->getRequest()->getParam('order');

        $result = json_encode(array(
            'timestamp' => time(),
            'order_id' => $orderId,
            'result' => $this->message,
        ));
        if ($this->error) {
            header('HTTP/1.1 400 Bad Request', true, 400);
        } else {
            header('HTTP/1.1 200 Ok', true, 200);
        }
        header('Content-Type: application/json', true);
        header('Content-Length: ' . strlen($result));

        return $this->_response($result);
    }
    /**
     * We receive a redirection
     *
     * we have to be sure that the payment has been successful for
     * the cart. So we will ask directly paga+tarde for it. Then if
     * true we will validate the order with the payment details.
     */
    public function redirect()
    {
        $orderId = Mage::app()->getRequest()->getParam('order');
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->error) {
            $this->restoreCart($order);
            if ($this->toCancel) {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_CANCELED,
                    Mage_Sales_Model_Order::STATE_CANCELED,
                    null,
                    false
                );
                try {
                    $order->save();
                } catch (\Exception $exception) {
                    $this->_redirect('checkout/cart');
                }
            }
            $this->_redirect('checkout/cart');
        } else {
            $this->_redirect('checkout/onepage/success');
        }
    }
    /**
     * Process validation vs API of pmt
     */
    public function processValidation()
    {
        $orderId = Mage::app()->getRequest()->getParam('order');
        /** @var Mage_Sales_Model_Order$order */
        $order = Mage::getModel('sales/order')->load($orderId);
        $payment = $order->getPayment();
        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        $env = $moduleConfig['PAYLATER_PROD'] ? 'PROD' : 'TEST';
        $privateKey = $moduleConfig['PAYLATER_PRIVATE_KEY_'.$env];

        if ($this->getOrderInPmtPayed($orderId, $privateKey)) {
            if (intval($order->getGrandTotal()*100) == $this->getOrderAmountInPmt($orderId, $privateKey)) {
                if ($order->canInvoice()) {
                    $invoice = $order->prepareInvoice();
                    if ($invoice->getGrandTotal() > 0) {
                        $invoice
                            ->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE)
                            ->register();
                        $order->addRelatedObject($invoice);
                        $payment->setCreatedInvoice($invoice);
                        Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder())
                            ->save();
                    }
                }
                $order->setState(
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    null,
                    true
                );
                $order->sendNewOrderEmail();
                try {
                    $order->save();
                } catch (\Exception $exception) {
                    throw new \Exception('Unable to save');
                }
                return true;
            }
            throw new \Exception('Amount is invalid');
        }
        throw new \Exception('Payment not processed');
    }

    /**
     * @param $order
     *
     * @return bool
     */
    private function restoreCart($order)
    {
        try {
            $cart = Mage::getSingleton('checkout/cart');
            $items = $order->getItemsCollection();
            if ($cart->getItemsCount() <= 0) {
                foreach ($items as $item) {
                    $cart->addOrderItem($item);
                }
                $cart->save();
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $orderId
     * @param $privateKey
     *
     * @return bool
     */
    protected function getOrderInPmtPayed($orderId, $privateKey)
    {
        $pmtClient = new \PagaMasTarde\PmtApiClient($privateKey);

        return $pmtClient->charge()->validatePaymentForOrderId($orderId);
    }

    /**
     * @param $orderId
     * @param $privateKey
     *
     * @return int
     */
    protected function getOrderAmountInPmt($orderId, $privateKey)
    {
        $pmtClient = new \PagaMasTarde\PmtApiClient($privateKey);
        $payments = $pmtClient->charge()->getChargesByOrderId($orderId);
        $latestCharge = array_shift($payments);

        return $latestCharge->getAmount();
    }
}
