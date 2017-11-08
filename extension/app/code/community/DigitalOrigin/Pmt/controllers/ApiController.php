<?php

/**
 * Class DigitalOrigin_Pmt_ApiController
 */
class DigitalOrigin_Pmt_ApiController extends Mage_Core_Controller_Front_Action
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
     * Controller index method:
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
            echo $result;

            return;
        }

        $userId = Mage::app()->getRequest()->getParam('user_id');
        $from = Mage::app()->getRequest()->getParam('from');
        $payment = Mage::app()->getRequest()->getParam('payment');

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
        $this->jsonResponse();
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

        echo $result;

        return;
    }
    /**
     * @return bool|null
     */
    public function authorize()
    {
        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        $env = $moduleConfig['PAYLATER_PROD'] ? 'PROD' : 'TEST';
        $privateKey = $moduleConfig['PAYLATER_PRIVATE_KEY_'.$env];

        if (Mage::app()->getRequest()->getParam('secret') == $privateKey ||
            Mage::app()->getRequest()->getHeader('secret') == $privateKey
        ) {
            return true;
        }

        return false;
    }
}
