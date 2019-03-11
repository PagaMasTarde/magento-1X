<?php

namespace Test;

use Httpful\Request;
use Httpful\Mime;
use Httpful\Http;

/**
 * Class ControllerTest
 * @package Test
 *
 * @group magento-controllers
 */
class ControllerTest extends MagentoTest
{
    /**
     * log route
     */
    const LOG_FOLDER = '/pmt/log/download';

    /**
     * config route
     */
    const CONFIG_FOLDER = '/pmt/config/';

    protected $configs = array(
        "PMT_TITLE",
        "PMT_SIMULATOR_DISPLAY_TYPE",
        "PMT_SIMULATOR_DISPLAY_SKIN",
        "PMT_SIMULATOR_DISPLAY_POSITION",
        "PMT_SIMULATOR_START_INSTALLMENTS",
        "PMT_SIMULATOR_CSS_POSITION_SELECTOR",
        "PMT_SIMULATOR_DISPLAY_CSS_POSITION",
        "PMT_SIMULATOR_CSS_PRICE_SELECTOR",
        "PMT_SIMULATOR_CSS_QUANTITY_SELECTOR",
        "PMT_FORM_DISPLAY_TYPE",
        "PMT_DISPLAY_MIN_AMOUNT",
        "PMT_URL_OK",
        "PMT_URL_KO",
    );

    /**
     * Test testLogDownload
     */
    public function testLogDownload()
    {
        $logUrl = self::MAGENTO_URL.self::LOG_FOLDER.'?secret='.$this->configuration['secretKey'];
        $response = Request::get($logUrl)->expects('json')->send();
        $this->assertEquals(3, count($response->body));
        $this->quit();
    }

    /**
     * Test testSetConfig
     */
    public function testSetConfig()
    {
        $notifyUrl = self::MAGENTO_URL.self::CONFIG_FOLDER.'post?secret='.$this->configuration['secretKey'];
        $body = array('PMT_TITLE' => 'changed');
        $response = Request::post($notifyUrl)
            ->body($body, Mime::FORM)
            ->expectsJSON()
            ->send();
        $this->assertEquals('changed', $response->body->PMT_TITLE);
        $this->quit();
    }

    /**
     * Test testGetConfig
     */
    public function testGetConfigs()
    {
        $notifyUrl = self::MAGENTO_URL.self::CONFIG_FOLDER.'get?secret='.$this->configuration['secretKey'];
        $response = Request::get($notifyUrl)->expects('json')->send();

        foreach ($this->configs as $config) {
            $this->assertArrayHasKey($config, (array) $response->body);
        }
        $this->quit();
    }
}
