<?php

/**
 * Class Pagantis_Pagantis_Block_Adminhtml_LogoField
 */
class Pagantis_Pagantis_Block_Adminhtml_LogoField extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
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
        return '<div class="adminLogo '. substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2) .'">
              <p class="description">'.$this->__("Pagantis is an online financing platform.").'</p>
              <p class="description"><a href="https://bo.pagantis.com" target="_blank">'.$this->__("Login to the Pagantis panel").'</a>&nbsp;
              <a href="https://developer.pagantis.com/platforms/#magento-1-x" target="_blank">'.$this->__("Documentation").'</a></p></div>';
    }
}
