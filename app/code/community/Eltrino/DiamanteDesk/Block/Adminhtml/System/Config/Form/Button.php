<?php
/**
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */

class Eltrino_DiamanteDesk_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('eltrino/diamantedesk/sytem/config/button.phtml');
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id' => 'diamantedesk_ckeck_connection',
                'label' => $this->__('Check Connection'),
                'onclick' => 'javascript:diamanteDeskCheckConnection(); return false;'
            ));

        return $button->toHtml();
    }

    public function getCheckConnectionUrl()
    {
        return Mage::getUrl('adminhtml/diamantedesk/checkConnection');
    }
}