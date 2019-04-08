<?php

require_once(__DIR__.'/../../../../../../lib/Pagantis/autoload.php');
require_once(__DIR__.'/AbstractController.php');

/**
 * Class Pagantis_Pagantis_ApiController
 */
class Pagantis_Pagantis_ApiController extends AbstractController
{
    /**
     * @var string $message
     */
    protected $message;
    /**
     * @var bool $error
     */
    protected $error = false;

    /**
     * Controller index method
     *
     * @return mixed
     * @throws Zend_Controller_Request_Exception
     */
    public function indexAction()
    {
        if (!$this->authorize()) {
            $result = json_encode(array(
                'timestamp' => time(),
                'result' => 'Access Forbidden',
            ));
            header('HTTP/1.1 403 Forbidden', true, 403);
            header('Content-Type: application/json', true);
            header('Content-Length: ' . strlen($result));

            return $this->_response($result);
        }
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = Mage::app()->getRequest();
        $userId = $request->getParam('user_id');
        $from = $request->getParam('from');
        $payment = $request->getParam('payment');

        /** @var Mage_Catalog_Model_Resource_Collection_Abstract $orders */
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->join(
            array('payment' => 'sales/order_payment'),
            'main_table.entity_id=payment.parent_id',
            array('payment_method' => 'payment.method')
        );
        if ($userId) {
            $orders->addFieldToFilter('customer_id', $userId);
        }
        if ($payment) {
            $orders->addFieldToFilter('payment.method', $payment);
        }
        if ($from) {
            $orders->addFieldToFilter('created_at', array('gt' => $from));
        }

        foreach ($orders as $order) {
            $this->message[] = json_decode(Mage::helper('core')->jsonEncode($order->getData()), true);
        }
        return $this->jsonResponse();
    }
    /**
     * Send a jsonResponse
     */
    public function jsonResponse()
    {
        $result = json_encode(array(
            'timestamp' => time(),
            'result' => $this->message,
        ));
        header('HTTP/1.1 200 Ok', true, 200);
        header('Content-Type: application/json', true);
        header('Content-Length: ' . strlen($result));

        return $this->_response($result);
    }

    /**
     * @return bool
     * @throws Zend_Controller_Request_Exception
     */
    public function authorize()
    {
        $moduleConfig = Mage::getStoreConfig('payment/pagantis');
        $privateKey = $moduleConfig['pagantis_private_key'];

        if (Mage::app()->getRequest()->getParam('secret') == $privateKey ||
            Mage::app()->getRequest()->getHeader('secret') == $privateKey
        ) {
            return true;
        }

        return false;
    }
}
