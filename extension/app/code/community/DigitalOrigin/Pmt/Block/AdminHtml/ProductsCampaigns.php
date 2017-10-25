<?php

class DigitalOrigin_Pmt_Block_AdminHtml_ProductsCampaigns extends DigitalOrigin_Pmt_Block_AdminHtml_ProductCampaigns
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('aplazame/productsCampaigns.phtml');
    }
}
