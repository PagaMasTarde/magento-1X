<?php

require_once('lib/DigitalOrigin/autoload.php');
require_once('app/code/community/DigitalOrigin/Pmt/controllers/BaseController.php');

/**
 * Class DigitalOrigin_Pmt_NotifyController
 *
 * Now with orders
 */
class DigitalOrigin_Pmt_NotifyController extends BaseController
{
    /**
     * @var string $merchantOrderId
     */
    protected $merchantOrderId;

    /**
     * @var Mage_Sales_Model_Order $merchantOrder
     */
    protected $merchantOrder;

    /**
     * @var mixed $config
     */
    protected $config;


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

        return $this->redirect();
    }

    /**
     * @throws Exception
     */
    public function prepareVariables()
    {
        try {
            $this->merchantOrderId = Mage::app()->getRequest()->getParam('order');
            if ($this->merchantOrderId == '') {
                $this->code = 404;
                $this->message = BaseController::PV_NO_MERCHANT_ORDERID;
                throw new \Exception($this->message,  $this->code);
            }
            $this->config = Mage::getStoreConfig('payment/paylater');
        } catch (\Exception $exception) {
            $this->code = 500;
            $this->message = BaseController::PMO_ERR_MSG;

            throw new \Exception($this->message,  $this->code);
        }

    }

    /**
     * @throws Exception
     */
    public function checkConcurrency()
    {
        try {
            $this->unblockConcurrency();
            $this->blockConcurrency($this->merchantOrderId);
            // $this->processValidation();
            // $this->unblockConcurrency($orderId);
        } catch (\Exception $exception) {
            $this->code = 429;
            $this->message = BaseController::CC_ERR_MSG;

            throw new \Exception($this->message,  $this->code);
        }
    }

    /**
     * @throws Exception
     */
    public function getMerchantOrder()
    {
        try {
            /** @var Mage_Sales_Model_Order $order */
            $this->merchantOrder = Mage::getModel('sales/order')->loadByIncrementId($this->merchantOrderId);
        } catch (\Exception $exception) {
            $this->code = 404;
            $this->message = 'Unable to find merchant Order';

            throw new \Exception($this->message,  $this->code);
        }
    }

    private function getPmtOrder()
    {
        try {
            $this->orderClient = new Client($this->config['public_key'], $this->config['secret_key']);
            $this->pmtOrder = $this->orderClient->getOrder($this->pmtOrderId);
        } catch (\Exception $e) {
            $exceptionObject = new \stdClass();
            $exceptionObject->method = __FUNCTION__;
            $exceptionObject->status = '400';
            $exceptionObject->result = self::GPO_ERR_MSG;
            $exceptionObject->result_description = $e->getMessage();
            throw new \Exception(serialize($exceptionObject));
        }
    }

    private function getPmtOrderId()
    {
        try {
            $this->getPmtOrderIdDb();
            $this->getMagentoOrderId();
        } catch (\Exception $e) {
            $exceptionObject = new \stdClass();
            $exceptionObject->method= __FUNCTION__;
            $exceptionObject->status='404';
            $exceptionObject->result= self::GPOI_ERR_MSG;
            $exceptionObject->result_description = $e->getMessage();
            throw new \Exception(serialize($exceptionObject));
        }
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        //Flow Notify/OK Url
        //------------------
        //checkConcurrency()
        //getMerchantOrder()
        //getPmtOrderId()
        //getPmtOrder()
        //checkOrderStatus()
        //checkMerchantOrderStatus()
        //validateAmount()
        //processMerchantOrder()
        //ConfirmPmtOrder()

        try {
            $this->prepareVariables();
            $this->checkConcurrency();
            $this->getPmtOrderId();



        } catch (\Exception $exception) {
            $this->saveLog($exception, array(
                'pmtMessage' => $this->message,
                'order' => $this->orderId,
            ));
            $this->response(array('message' => $this->message), null, $this->code);
        }





        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $result = array(
                'timestamp' => time(),
                'order_id' => $orderId,
                'result' => $this->message,
            );
            $headers = array();
            return $this->response($result, $headers, 400);
        } else {
            return $this->redirect();
        }
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
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        $successUrl = $moduleConfig['PAYLATER_OK_URL'];
        $failureUrl = $moduleConfig['PAYLATER_KO_URL'];

        if ($this->error || $this->toCancel) {
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
                    $data = array(
                        'method' => __FUNCTION__,
                    );
                    if ($magentoOrder) {
                        $data['order_id'] = $orderId;
                    }

                    $this->saveLog($exception, $data);
                    $this->_redirectUrl(Mage::getUrl($failureUrl));
                }
            }
            $this->_redirectUrl(Mage::getUrl($failureUrl));
        } else {
            $this->_redirectUrl(Mage::getUrl($successUrl));
        }
    }
    /**
     * Process validation vs API of pmt
     */
    public function processValidation()
    {
        die("processValidation");
        $orderId = Mage::app()->getRequest()->getParam('order');
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $status = $order->getStatus();
        $payment = $order->getPayment();
        $code = $payment->getMethodInstance()->getCode();
        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        $env = $moduleConfig['PAYLATER_PROD'] ? 'PROD' : 'TEST';
        $publicKey  = $moduleConfig['PAYLATER_PUBLIC_KEY_'.$env];
        $privateKey = $moduleConfig['PAYLATER_PRIVATE_KEY_'.$env];

        // Check previous status is 'pending_payment'
        $statusHistory = $order->getAllStatusHistory();
        if (!(is_array($statusHistory) &&  is_object($statusHistory[0]) &&
            $statusHistory[0]->getStatus() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)) {
            $this->message = 'Payment already processed';
            return true;
        }

        // Order has been processed at least once in the past.
        foreach ($order->getAllStatusHistory() as $oStatus) {
            if (in_array($oStatus->getStatus(),array(Mage_Sales_Model_Order::STATE_PROCESSING, Mage_Sales_Model_Order::STATE_COMPLETE))) {
                $this->message = 'Order has been processed previously"';
                return true;
            }
        }

        $orderClient = new \PagaMasTarde\OrdersApiClient\Client(
            $publicKey,
            $privateKey
        );
        var_dump($orderClient);die;
        if ($status == Mage_Sales_Model_Order::STATE_PROCESSING ||
            $status == Mage_Sales_Model_Order::STATE_COMPLETE ||
            $code != self::CODE
        ) {
            $this->message = 'Payment already processed';
            return true;
        }

        if ($this->getOrderInPmtPayed($orderId, $privateKey)) {
            $pmtAmount = $this->getOrderAmountInPmt($orderId, $privateKey);
            if (intval($order->getGrandTotal()*100) == $pmtAmount) {
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
                    Mage::log(
                        json_encode(array(
                            'order' => $orderId,
                            'timestamp' => time(),
                            'message' => $exception->getMessage(),
                            'trace' => $exception->getTrace(),
                        )),
                        null,
                        'pmt.log',
                        true
                    );
                    throw new \Exception('Unable to save order');
                }
                $this->message = 'Payment Processed';
                return true;
            }
            $this->triggerAmountPaymentError($order, $pmtAmount);
            return true;
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
        } catch (\Exception $exception) {
            $data = array(
                'method' => __FUNCTION__,
            );
            if ($magentoOrder) {
                $data['cart_id'] = cartId;
            }

            $this->saveLog($exception, $data);
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

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @throws Exception
     */
    protected function triggerAmountPaymentError(Mage_Sales_Model_Order $order, $amount)
    {
        die("triggerAmountPaymentError");
        $order->setState(
            Mage_Sales_Model_Order::STATUS_FRAUD,
            Mage_Sales_Model_Order::STATUS_FRAUD,
            'There is a difference between PMT and Magento, please conciliate manually in PMT backoffice.
             (pmt-amount: ' . $amount . ')',
            false
        );
        try {
            $this->message = 'Fraud in order, check magento backOffice';
            $order->save();
        } catch (\Exception $exception) {
            Mage::log(
                json_encode(array(
                    'order' => $order->getId(),
                    'timestamp' => time(),
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace(),
                )),
                null,
                'pmt.log',
                true
            );
            throw new \Exception('Unable to save wrong amount order');
        }
    }

    /**
     * @param $orderId
     *
     * @return bool
     */
    protected function blockConcurrency($orderId)
    {
        $sql = "INSERT INTO " . self::CONCURRENCY_TABLE . " VALUE (" . $orderId. "," . time() . ")";

        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $conn->query($sql);
    }

    /**
     * @param null $orderId
     *
     * @return bool
     */
    protected function unblockConcurrency($orderId = null)
    {
        if ($orderId == null) {
            $sql = "DELETE FROM " . self::CONCURRENCY_TABLE . " WHERE timestamp <" . (time() - 10);
        } else {
            $sql = "DELETE FROM " . self::CONCURRENCY_TABLE . " WHERE id  = " . $orderId;
        }

        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $conn->query($sql);
    }
}
