<?php

require_once('lib/DigitalOrigin/autoload.php');
require_once('app/code/community/DigitalOrigin/Pmt/controllers/AbstractController.php');

use PagaMasTarde\OrdersApiClient\Client as PmtClient;
use PagaMasTarde\OrdersApiClient\Model\Order as PmtModelOrder;

/**
 * Class DigitalOrigin_Pmt_NotifyController
 *
 * Now with orders
 */
class DigitalOrigin_Pmt_NotifyController extends AbstractController
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
     * @var string $pmtOrderId
     */
    protected $pmtOrderId;

    /**
     * @var \PagaMasTarde\OrdersApiClient\Model\Order $pmtOrder
     */
    protected $pmtOrder;

    /**
     * @var PagaMasTarde\OrdersApiClient\Client $orderClient
     */
    protected $orderClient;

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
            $this->merchantOrderId = Mage::app()->getRequest()->getParam('order');
            if ($this->merchantOrderId == '') {
                throw new \Exception(self::CC_NO_MERCHANT_ORDERID, 404);
            }

            try {
                $config = Mage::getStoreConfig('payment/paylater');
                $env = $moduleConfig['PAYLATER_PROD'] ? 'PROD' : 'TEST';
                $this->config = array(
                    'urlOK' => $config['PAYLATER_OK_URL'],
                    'urlKO' => $config['PAYLATER_KO_URL'],
                    'publicKey' => $config['PAYLATER_PUBLIC_KEY_' . $env],
                    'privateKey' => $config['PAYLATER_PRIVATE_KEY_' . $env],
                );
            } catch (\Exception $exception) {
                throw new \Exception(self::CC_NO_CONFIG, 500);
            }
    }

    /**
     * @throws Exception
     */
    public function checkConcurrency()
    {
        try {
            $this->prepareVariables();
            $this->unblockConcurrency();
            $this->blockConcurrency($this->merchantOrderId);
        } catch (\Exception $exception) {
            $this->statusCode = 429;
            $this->errorMessage = self::CC_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
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
            $this->statusCode = 404;
            $this->errorMessage = self::GMO_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    private function getPmtOrderId()
    {
        try {
            $sql = 'select pmt_order_id from ' . self::PMT_ORDERS_TABLE .
                   ' where mg_order_id = \'' . $this->merchantOrderId . '\'';

            $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
            $result = $conn->fetchAll($sql);
            if (count($result) !== 1 && isset($result[0]) && isset($result[0]['pmt_order_id'])) {
                throw new \Exception(self::GPOI_NO_ORDERID, 404);
            }

            $this->pmtOrderId = $result[0]['pmt_order_id'];
        } catch (\Exception $exception) {
            $this->statusCode = 404;
            $this->errorMessage = self::GPOI_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    private function getPmtOrder()
    {
        try {
            $this->orderClient = new PmtClient($this->config['publicKey'], $this->config['privateKey']);
            $this->pmtOrder = $this->orderClient->getOrder($this->pmtOrderId);
            if (!($this->pmtOrder instanceof PmtModelOrder)) {
                throw new \Exception(self::GPO_ERR_TYPEOF, 500);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 400;
            $this->errorMessage = self::GPO_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    public function checkOrderStatus()
    {
        try {
            if ($this->pmtOrder->getStatus() !== PmtModelOrder::STATUS_AUTHORIZED) {
                throw new \Exception(self::COS_WRONG_STATUS, 403);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 403;
            $this->errorMessage = self::COS_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    public function checkMerchantOrderStatus()
    {
        try {
            // Check previous status is 'pending_payment'
            $statusHistory = $this->merchantOrder->getAllStatusHistory();
            if (!(is_array($statusHistory) &&  is_object($statusHistory[0]) &&
                $statusHistory[0]->getStatus() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)) {
                throw new \Exception( self::CMOS_WRONG_PREVIOUS_STATUS, 409);
            }

            // Order has been processed at least once in the past.
            foreach ($this->merchantOrder->getAllStatusHistory() as $oStatus) {
                if (in_array($oStatus->getStatus(),
                             array(Mage_Sales_Model_Order::STATE_PROCESSING,
                             Mage_Sales_Model_Order::STATE_COMPLETE))) {
                    throw new \Exception( self::CMOS_PREVIOUSLY_PROCESSED, 409);
                }
            }

            // Check current state
            $status = $this->merchantOrder->getStatus();
            if ($status == Mage_Sales_Model_Order::STATE_PROCESSING ||
                $this->merchantOrder->getPayment()->getMethodInstance()->getCode() != self::PMT_CODE
            ) {
                throw new \Exception( self::CMOS_WRONG_CURRENT_STATUS, 409);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 409;
            $this->errorMessage = self::CMOS_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    public function validateAmount()
    {
        try {
            if ($this->pmtOrder->getShoppingCart()->getTotalAmount() != intval($this->merchantOrder->getGrandTotal()*100)) {
                throw new \Exception(self::VA_WRONG_AMOUNT, 409);
            }
        } catch (\Exception $exception) {
            $this->statusCode = 409;
            $this->errorMessage = self::VA_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    public function processMerchantOrder()
    {
        try {
            if ($this->merchantOrder->canInvoice()) {
                $invoice = $this->merchantOrder->prepareInvoice();
                if ($invoice->getGrandTotal() > 0) {
                    $invoice
                        ->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE)
                        ->register();
                    $this->merchantOrder->addRelatedObject($invoice);
                    $payment = $this->merchantOrder->getPayment();
                    $payment->setCreatedInvoice($invoice);
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
                }
            }
            $this->merchantOrder->setState(
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage_Sales_Model_Order::STATE_PROCESSING,
                null,
                true
            );
            $this->merchantOrder->save();

            try {
                $this->merchantOrder->sendNewOrderEmail();
            } catch (\Exception $exception) {
                // Do nothing
            }

        } catch (\Exception $exception) {
            $this->statusCode = 500;
            $this->errorMessage = self::PMO_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    private function confirmPmtOrder()
    {
        try {
            $this->orderClient->confirmOrder($this->pmtOrderId);
        } catch (\Exception $exception) {
            $this->statusCode = 500;
            $this->errorMessage = self::CPO_ERR_MSG;
            $this->errorDetail = 'Code: '.$exception->getCode().', Description: '.$exception->getMessage();
            throw new \Exception();
        }
    }

    public function rollbackMerchantOrder()
    {
            $this->merchantOrder->setState(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                null,
                false
            );
            $this->merchantOrder->save();
    }

    public function indexAction()
    {
        //Flow Notify/OK Url
        try {
            $this->checkConcurrency();
            $this->getMerchantOrder();
            $this->getPmtOrderId();
            $this->getPmtOrder();
            $this->checkOrderStatus();
            $this->checkMerchantOrderStatus();
            $this->validateAmount();
            $this->processMerchantOrder();
        } catch (\Exception $exception) {
            return $this->cancelProcess($exception);
        }

        try {
            $this->confirmPmtOrder();
        } catch (\Exception $exception) {
            $this->rollbackMerchantOrder();
            return $this->cancelProcess($exception);
        }

        try {
            $this->unblockConcurrency($this->merchantOrderId);
        } catch (\Exception $exception) {
            // Do nothing
        }

        return $this->finishProcess(false);
    }

    public function cancelProcess(\Exception $exception)
    {
        $this->unblockConcurrency($this->merchantOrderId);
        $this->restoreCart();
        $method = debug_backtrace();
        $method = $method[1]['function'];
        $this->saveLog($exception, array(
            'pmtMessage' => $this->errorMessage,
            'pmtMessageDetail' => $this->errorDetail,
            'pmtOrderId' => $this->pmtOrderId,
            'merchantOrderId' => $this->merchantOrderId,
            'method' => $method,
        ));
        return $this->finishProcess(true);
    }

    public function finishProcess($error = true)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return $this->response();
        }
        return $this->redirect($error);
    }

    private function restoreCart()
    {
        try {
            if ($this->merchantOrder) {
                $cart = Mage::getSingleton('checkout/cart');
                $items = $this->merchantOrder->getItemsCollection();
                if ($cart->getItemsCount() <= 0) {
                    foreach ($items as $item) {
                        $cart->addOrderItem($item);
                    }
                    $cart->save();
                }
            }
        } catch (\Exception $exception) {
            // Do nothing
        }
    }

    /**
     * @param $orderId
     * @throws Exception
     */
    protected function blockConcurrency($orderId)
    {
        $sql = "INSERT INTO " . self::CONCURRENCY_TABLE . " VALUE (" . $orderId. "," . time() . ")";

        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $conn->query($sql);
    }

    /**
     * @param null $orderId
     * @throws Exception
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
