<?php


class Aplazame_Aplazame_Block_Payment_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $aplazameJsUri = getenv('APLAZAME_JS_URI') ? getenv('APLAZAME_JS_URI') : 'https://aplazame.com/static/aplazame.js';

        /** @var Aplazame_Aplazame_Model_Api_Client $client */
        $client = Mage::getModel('aplazame/api_client');

        /** @var Aplazame_Aplazame_Model_Payment $payment */
        $payment = Mage::getModel('aplazame/payment');

        $html = '
<html>
    <body style="margin: 0;">

        <script
            type="text/javascript"
            src="'. $aplazameJsUri . '"
            data-api-host="' . $client->apiBaseUri . '"
            data-aplazame="'. Mage::getStoreConfig('payment/aplazame/public_api_key') . '"
            data-sandbox="'. (Mage::getStoreConfig('payment/aplazame/sandbox')?'true':'false') . '">
        </script>

        <script>
            aplazame.checkout(' . json_encode(Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($payment->getCheckoutSerializer())) . ');
        </script>

        <iframe src="'./*Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)*/Mage::getUrl('', array('_secure'=>true)).'" style="position:fixed; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden;">
            Your browser does not support IFrames
        </iframe>
    </body>
</html>';

        return $html;
    }
}
