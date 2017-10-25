<?php

class Aplazame_Aplazame_ApiController extends Mage_Core_Controller_Front_Action
{
    public static function forbidden()
    {
        return array(
            'status_code' => 403,
            'payload' => array(
                'status' => 403,
                'type' => 'FORBIDDEN',
            ),
        );
    }

    public static function not_found()
    {
        return array(
            'status_code' => 404,
            'payload' => array(
                'status' => 404,
                'type' => 'NOT_FOUND',
            ),
        );
    }

    public static function collection($page, $page_size, array $elements)
    {
        return array(
            'status_code' => 200,
            'payload' => array(
                'query' => array(
                    'page' => $page,
                    'page_size' => $page_size,
                ),
                'elements' => $elements,
            ),
        );
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $path = $request->getParam('path', '');
        $pathArguments = json_decode($request->getParam('path_arguments', '[]'), true);
        $queryArguments = json_decode($request->getParam('query_arguments', '[]'), true);

        $result = $this->route($path, $pathArguments, $queryArguments);

        $response = $this->getResponse();
        $response->setHttpResponseCode($result['status_code']);
        $response->setHeader('Content-type', 'application/json');
        $response->setBody(json_encode(Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($result['payload'])));
    }

    /**
     * @param string $path
     * @param array $pathArguments
     * @param array $queryArguments
     *
     * @return array
     */
    public function route($path, array $pathArguments, array $queryArguments)
    {
        if (!$this->verifyAuthentication()) {
            return self::forbidden();
        }

        switch ($path) {
            case '/article/':
                $controller = new Aplazame_Aplazame_Api_Article(Mage::getModel('catalog/product'));

                return $controller->articles($queryArguments);
            case '/order/{order_id}/history/':
                $controller = new Aplazame_Aplazame_Api_Order(Mage::getModel('sales/order'));

                return $controller->history($pathArguments, $queryArguments);
            default:
                return self::not_found();
        }
    }

    /**
     * @return bool
     */
    private function verifyAuthentication()
    {
        $privateKey = Mage::getStoreConfig('payment/aplazame/secret_api_key');

        $authorization = $this->getHeaderAuthorization();
        if (!$authorization || empty($privateKey)) {
            return false;
        }

        return ($authorization === $privateKey);
    }

    private function getHeaderAuthorization()
    {
        $request = $this->getRequest();

        $header = $request->getHeader('authorization');
        if (!$header) {
            return false;
        }

        return trim(str_replace('Bearer', '', $header));
    }
}
