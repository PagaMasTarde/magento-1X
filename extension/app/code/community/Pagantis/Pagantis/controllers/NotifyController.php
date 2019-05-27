<?php

require_once(__DIR__.'/../../../../../../lib/Pagantis/autoload.php');
require_once(__DIR__.'/AbstractController.php');

use Pagantis\OrdersApiClient\Client as PagantisClient;
use Pagantis\OrdersApiClient\Model\Order as PagantisModelOrder;
use Pagantis\ModuleUtils\Exception\AlreadyProcessedException;
use Pagantis\ModuleUtils\Exception\AmountMismatchException;
use Pagantis\ModuleUtils\Exception\ConcurrencyException;
use Pagantis\ModuleUtils\Exception\MerchantOrderNotFoundException;
use Pagantis\ModuleUtils\Exception\NoIdentificationException;
use Pagantis\ModuleUtils\Exception\OrderNotFoundException;
use Pagantis\ModuleUtils\Exception\QuoteNotFoundException;
use Pagantis\ModuleUtils\Exception\UnknownException;
use Pagantis\ModuleUtils\Exception\WrongStatusException;
use Pagantis\ModuleUtils\Model\Response\JsonSuccessResponse;
use Pagantis\ModuleUtils\Model\Response\JsonExceptionResponse;

/**
 * Class Pagantis_Pagantis_NotifyController
 *
 * Now with orders
 */
class Pagantis_Pagantis_NotifyController extends AbstractController
{

    /** Concurrency tablename */
    const CONCURRENCY_TABLENAME = 'pmt_cart_concurrency';

    /**
     * @var string $merchantOrderId
     */
    protected $merchantOrderId;

    /**
     * @var Mage_Sales_Model_Order $merchantOrder
     */
    protected $merchantOrder;

    /**
     * @var string $pagantisOrderId
     */
    protected $pagantisOrderId;

    /**
     * @var PagantisModelOrder $pagantisOrder
     */
    protected $pagantisOrder;

    /**
     * @var PagantisClient $orderClient
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
        try {
            $this->toCancel = true;
            $this->prepareVariables();
            $this->getMerchantOrder();
            $this->restoreCart();
            return $this->redirect(true);
        } catch (Exception $exception) {
            return $this->redirect(true);
        }
    }

    /**
     * Main action of the controller. Dispatch the Notify process
     *
     * @return Mage_Core_Controller_Varien_Action|void
     * @throws Exception
     */
    public function indexAction()
    {
        $jsonResponse = array();
        try {
            $this->checkConcurrency();
            $this->getMerchantOrder();
            $this->getPagantisOrderId();
            $this->getPagantisOrder();
            $this->checkOrderStatus();
            $this->checkMerchantOrderStatus();
            $this->validateAmount();
            $this->processMerchantOrder();
        } catch (Exception $exception) {
            $jsonResponse = new JsonExceptionResponse();
            $jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $jsonResponse->setPagantisOrderId($this->pagantisOrderId);
            $jsonResponse->setException($exception);
            $response = $jsonResponse->toJson();
            $this->cancelProcess($exception);
        }

        try {
            if (!isset($response)) {
                $this->confirmPagantisOrder();
                $jsonResponse = new JsonSuccessResponse();
                $jsonResponse->setMerchantOrderId($this->merchantOrderId);
                $jsonResponse->setPagantisOrderId($this->pagantisOrderId);
            }
        } catch (Exception $exception) {
            $this->rollbackMerchantOrder();
            $jsonResponse = new JsonExceptionResponse();
            $jsonResponse->setMerchantOrderId($this->merchantOrderId);
            $jsonResponse->setPagantisOrderId($this->pagantisOrderId);
            $jsonResponse->setException($exception);
            $jsonResponse->toJson();
            $this->cancelProcess($exception);
        }

        try {
            $this->unblockConcurrency($this->merchantOrderId);
        } catch (Exception $exception) {
            // Do nothing
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return $jsonResponse->printResponse();
        } else {
            $error = (!isset($response)) ? false : true;
            return $this->redirect($error);
        }
    }

    /**
     * Find and init variables needed to process payment
     *
     * @throws Exception
     */
    public function prepareVariables()
    {
        $this->merchantOrderId = Mage::app()->getRequest()->getParam('order');
        if ($this->merchantOrderId == '') {
            throw new QuoteNotFoundException();
        }

        try {
            $config = Mage::getStoreConfig('payment/pagantis');
            $extraConfig = Mage::helper('pagantis/ExtraConfig')->getExtraConfig();
            $this->config = array(
                'urlOK' => $extraConfig['PAGANTIS_URL_OK'],
                'urlKO' => $extraConfig['PAGANTIS_URL_KO'],
                'publicKey' => $config['pagantis_public_key'],
                'privateKey' => $config['pagantis_private_key'],
            );
        } catch (Exception $exception) {
            throw new UnknownException('Unable to load module configuration');
        }
    }

    /**
     * Check the concurrency of the purchase
     *
     * @throws Exception
     */
    public function checkConcurrency()
    {
        $this->prepareVariables();
        $this->unblockConcurrency();
        $this->blockConcurrency($this->merchantOrderId);
    }

    /**
     * Retrieve the merchant order by id
     *
     * @throws Exception
     */
    public function getMerchantOrder()
    {
        try {
            /** @var Mage_Sales_Model_Order $order */
            $this->merchantOrder = Mage::getModel('sales/order')->loadByIncrementId($this->merchantOrderId);
        } catch (Exception $exception) {
            throw new MerchantOrderNotFoundException();
        }
    }

    /**
     * Find Pagantis Order Id in AbstractController::PAGANTIS_ORDERS_TABLE
     *
     * @throws Exception
     */
    private function getPagantisOrderId()
    {
        try {
            $this->createTableIfNotExists('pagantis/order');
            $model = Mage::getModel('pagantis/order');
            $model->load($this->merchantOrderId, 'mg_order_id');

            $this->pagantisOrderId = $model->getPagantisOrderId();
            if (is_null($this->pagantisOrderId)) {
                throw new NoIdentificationException();
            }
        } catch (Exception $exception) {
            throw new NoIdentificationException();
        }
    }

    /**
     * Find Pagantis Order in Orders Server using Pagantis\OrdersApiClient
     *
     * @throws Exception
     */
    private function getPagantisOrder()
    {
        $this->orderClient = new PagantisClient($this->config['publicKey'], $this->config['privateKey']);
        $this->pagantisOrder = $this->orderClient->getOrder($this->pagantisOrderId);
        if (!($this->pagantisOrder instanceof PagantisModelOrder)) {
            throw new OrderNotFoundException();
        }
    }

    /**
     * Compare statuses of merchant order and Pagantis order, witch have to be the same.
     *
     * @throws Exception
     */
    public function checkOrderStatus()
    {
        if ($this->pagantisOrder->getStatus() !== PagantisModelOrder::STATUS_AUTHORIZED) {
            if ($this->merchantOrder->getStatus() == Mage_Sales_Model_Order::STATE_PROCESSING) {
                throw new AlreadyProcessedException();
            }

            if ($this->pagantisOrder instanceof PagantisModelOrder) {
                $status = $this->pagantisOrder->getStatus();
            } else {
                $status = '-';
            }
            throw new WrongStatusException($status);
        }
    }

    /**
     * Check that the merchant order was not previously processes and is ready to be paid
     *
     * @throws Exception
     */
    public function checkMerchantOrderStatus()
    {
        // Check previous status is 'pending_payment'
        $statusHistory = $this->merchantOrder->getAllStatusHistory();
        if (!(is_array($statusHistory) &&  is_object($statusHistory[0]) &&
            $statusHistory[0]->getStatus() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)) {
            throw new WrongStatusException($statusHistory[0]->getStatus());
        }

        // Order has been processed at least once in the past.
        foreach ($this->merchantOrder->getAllStatusHistory() as $oStatus) {
            if (in_array(
                $oStatus->getStatus(),
                array(Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage_Sales_Model_Order::STATE_COMPLETE)
            )
            ) {
                throw new WrongStatusException($oStatus->getStatus());
            }
        }

        // Check current state
        $status = $this->merchantOrder->getStatus();
        if ($status == Mage_Sales_Model_Order::STATE_PROCESSING ||
            $this->merchantOrder->getPayment()->getMethodInstance()->getCode() != self::PAGANTIS_CODE
        ) {
            throw new WrongStatusException($status);
        }
    }

    /**
     * Check that the merchant order and the order in Pagantis have the same amount to prevent hacking
     *
     * @throws Exception
     */
    public function validateAmount()
    {
        $pagantisAmount = $this->pagantisOrder->getShoppingCart()->getTotalAmount();
        $merchantAmount = intval($this->merchantOrder->getGrandTotal()*100);
        if ($pagantisAmount != $merchantAmount) {
            throw new AmountMismatchException($pagantisAmount, $merchantAmount);
        }
    }

    /**
     * Process the merchant order and notify client
     *
     * @throws Exception
     */
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
                'pagantisOrderId: ' . $this->pagantisOrder->getId(). ' ' .
                'pagantisOrderStatus: '. $this->pagantisOrder->getStatus(),
                true
            );
            $this->merchantOrder->save();

            try {
                $this->merchantOrder->sendNewOrderEmail();
            } catch (Exception $exception) {
                // Do nothing
            }
        } catch (Exception $exception) {
            throw new UnknownException($exception->getMessage());
        }
    }

    /**
     * Confirm the order in Pagantis
     *
     * @throws Exception
     */
    private function confirmPagantisOrder()
    {
        try {
            $this->orderClient->confirmOrder($this->pagantisOrderId);
        } catch (Exception $exception) {
            $this->pagantisOrder = $this->orderClient->getOrder($this->pagantisOrderId);
            if ($this->pagantisOrderId->getStatus() !== \Pagantis\OrdersApiClient\Model\Order::STATUS_CONFIRMED) {
                $this->saveLog($exception);
                throw new UnknownException($exception->getMessage());
            } else {
                $logEntry= new \Pagantis\ModuleUtils\Model\Log\LogEntry();
                $logEntry->info(
                    'Concurrency issue: Order_id '.$this->pagantisOrderId.' was confirmed by other process'
                );
                $model = Mage::getModel('pagantis/log');
                $model->setData(array('log' => $logEntry->toJson()));
                $model->save();
            }
        }
    }

    /**
     * Leave the merchant order as it was peviously
     *
     * @throws Exception
     */
    public function rollbackMerchantOrder()
    {
            $this->merchantOrder->setState(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                'pagantisOrderId: ' . $this->pagantisOrder->getId(). ' ' .
                'pagantisOrderStatus: '. $this->pagantisOrder->getStatus(),
                false
            );
            $this->merchantOrder->save();
    }

    /**
     *  Do all the necessary actions to cancel the confirmation process in case of error
     * 1. Unblock concurrency
     * 2. Restore the cart if possible
     * 3. Save log
     *
     * @param Exception $exception
     * @throws Exception
     */
    public function cancelProcess(Exception $exception)
    {
        $this->unblockConcurrency($this->merchantOrderId);
        $this->restoreCart();
        $this->saveLog($exception);
    }

    /**
     * Restore the cart of the order
     */
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
        } catch (Exception $exception) {
            // Do nothing
        }
    }

    /**
     * Lock the concurrency to prevent duplicated inputs
     *
     * @param $orderId
     *
     * @return bool
     * @throws ConcurrencyException
     */
    protected function blockConcurrency($orderId)
    {
        $sql = "INSERT INTO  " . self::CONCURRENCY_TABLENAME . "  VALUE (" . $orderId. "," . time() . ")";

        try {
            $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
            $conn->query($sql);
        } catch (Exception $e) {
            throw new ConcurrencyException();
        }

        return true;
    }

    /**
     * Unlock the concurrency
     *
     * @param null $orderId
     *
     * @return bool
     * @throws ConcurrencyException
     */
    protected function unblockConcurrency($orderId = null)
    {
        if ($orderId == null) {
            $sql = "DELETE FROM " . self::CONCURRENCY_TABLENAME . " WHERE timestamp <" . (time() - 10);
        } else {
            $sql = "DELETE FROM " . self::CONCURRENCY_TABLENAME . " WHERE id  = " . $orderId;
        }
        try {
            $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
            $conn->query($sql);
        } catch (Exception $e) {
            throw new ConcurrencyException();
        }
        return true;
    }
}
