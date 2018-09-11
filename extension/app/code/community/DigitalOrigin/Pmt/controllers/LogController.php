<?php

/**
 * Class DigitalOrigin_Pmt_LogController
 */
class DigitalOrigin_Pmt_LogController extends Mage_Core_Controller_Front_Action
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
     *
     * @return Mage_Core_Controller_Response_Http
     * @throws Zend_Controller_Request_Exception
     */
    public function indexAction()
    {
        if (!$this->authorize()) {
            $result = json_encode(array('timestamp' => time(), 'result' => 'Access Forbidden'));
            $headers = array(
                'HTTP/1.1 403 Forbidden' => 403,
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($result)
            );
            return $this->response($result, $headers);
        }

        $result = array('timestamp' => time(), result => array());
        $logDir  = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'log';
        if (is_dir($logDir)) {
            $pmtLogFile = $logDir . DIRECTORY_SEPARATOR . 'pmt.log';
            $prepmtLogFile = $logDir . DIRECTORY_SEPARATOR . 'pre-pmt.log';
            if (file_exists($pmtLogFile)) {
                $result['result']['pmt'] = file_get_contents($pmtLogFile);
            }
            if (file_exists($prepmtLogFile)) {
                $result['result']['pre-pmt'] = file_get_contents($prepmtLogFile);
            }
        }

        $result = json_encode($result);
        $headers = array(
            'HTTP/1.1 200 Ok' => 200,
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($result)
        );
        return $this->response($result, $headers);
    }

    /**
     * Check if private key is provided and is equal than setted in backoffice
     *
     * @return bool
     * @throws Zend_Controller_Request_Exception
     */
    public function authorize()
    {
        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        $env = $moduleConfig['PAYLATER_PROD'] ? 'PROD' : 'TEST';
        $privateKey = $moduleConfig['PAYLATER_PRIVATE_KEY_'.$env];
        if ((Mage::app()->getRequest()->getParam('secret') == $privateKey ||
            Mage::app()->getRequest()->getHeader('secret') == $privateKey)
            && !empty($privateKey)) {
            return true;
        }

        return false;
    }

    /**
     * Return a printable response of the request
     *
     * @param string $result
     * @param array  $headers
     * @return Mage_Core_Controller_Response_Http
     */
    public function response($result = '', $headers = array()) {
        $response = $this->getResponse();
        $response->setBody($result);
        foreach ($headers as $key => $value) {
            $response->setHeader($key, $value);
        }
        return $response;
    }
}
