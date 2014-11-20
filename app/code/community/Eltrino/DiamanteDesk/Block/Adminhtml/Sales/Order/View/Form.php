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

class Eltrino_DiamanteDesk_Block_Adminhtml_Sales_Order_View_Form extends Eltrino_DiamanteDesk_Block_Form
{
    public function _construct()
    {
        $this->setTemplate('eltrino/diamantedesk/simpleForm.phtml');
        $this->setShowBackLink(false);
    }

    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/diamantedesk/simpleFormSubmit');
    }

}