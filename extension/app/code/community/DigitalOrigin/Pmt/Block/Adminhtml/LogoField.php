<?php

/**
 * Class DigitalOrigin_Pmt_Block_Adminhtml_LogoField
 */
class DigitalOrigin_Pmt_Block_Adminhtml_LogoField extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return header comment part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '<div class="adminLogo">
              <p class="description">'.$this->__("Paga+Tarde is an online financing platform.").'</p>
              <p class="description"><a href="https://bo.pagamastarde.com" target="_blank">'.$this->__("Login to the Paga+Tarde pannel").'</a>&nbsp;
              <a href="http://docs.pagamastarde.com/" target="_blank">'.$this->__("Documentation").'</a></p></div>';
    }
}
