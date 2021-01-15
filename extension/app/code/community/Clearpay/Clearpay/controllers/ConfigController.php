<?php

require_once(__DIR__.'/AbstractController.php');

/**
 * Class Clearpay_Clearpay_ConfigController
 */
class Clearpay_Clearpay_ConfigController extends AbstractController
{
    /**
     * @var bool $error
     */
    protected $error = false;

    /**
     * Controller get method:
     *
     * @return Mage_Core_Controller_Response_Http
     * @throws Zend_Controller_Request_Exception
     */
    public function getAction()
    {
        if (!$this->authorize()) {
            $this->errorMessage = 'Access Forbidden';
            $this->statusCode = 403;
            return $this->response();
        }

        $results = Mage::helper('clearpay/ExtraConfig')->getExtraConfig();
        $this->statusCode = 200;
        return $this->response($results);
    }

    /**
     * Controller post method:
     *
     * @return Mage_Core_Controller_Response_Http
     * @throws Zend_Controller_Request_Exception
     */
    public function postAction()
    {
        if (!$this->authorize()) {
            $this->errorMessage = 'Access Forbidden';
            $this->statusCode = 403;
            return $this->response();
        }

        $output = Mage::helper('clearpay/ExtraConfig')->getExtraConfig();
        if (count($_POST)) {
            $post = $_POST;
            $tableName = Mage::getSingleton('core/resource')->getTableName("clearpay_config");
            $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
            foreach ($output as $key => $value) {
                if (array_key_exists($key, $post)) {
                    $sql = 'update ' . $tableName . ' set value = \''. $post[$key] .'\' 
                            where config = \'' . $key . '\'';
                    $conn->query($sql);
                    $value = $post[$key];
                    unset($post[$key]);
                }
                $output[$key] = $value;
            }
        } else {
            $post['NO_POST_DATA'] = 'No post data provided';
        }

        if (count($post) > 0) {
            $output['__ERRORS__'] = $post;
        }
        $this->statusCode = 200;
        return $this->response($output);
    }

    /**
     * Check if private key is provided and is equal than setted in backoffice
     *
     * @return bool
     * @throws Zend_Controller_Request_Exception
     */
    public function authorize()
    {
        $moduleConfig = Mage::getStoreConfig('payment/clearpay');
        $privateKey = $moduleConfig['clearpay_private_key'];
        if ((Mage::app()->getRequest()->getParam('secret') == $privateKey ||
            Mage::app()->getRequest()->getHeader('secret') == $privateKey)
            && !empty($privateKey)) {
            return true;
        }

        return false;
    }
}
