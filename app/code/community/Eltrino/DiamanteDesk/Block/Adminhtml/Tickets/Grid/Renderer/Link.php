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

class Eltrino_DiamanteDesk_Block_Adminhtml_Tickets_Grid_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    const LINK_TO_DIAMANTEDESK_TICKET_BACKEND = '/desk/tickets/view/';

    public function render(Varien_Object $row)
    {
        $domainName = Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_API_SERVER_ADDRESS);
        return sprintf('<a href="%s" target="_blank">%s</a>',
            trim($domainName, '/') .
            static::LINK_TO_DIAMANTEDESK_TICKET_BACKEND .
            $row->getKey(),
            Mage::helper('eltrino_diamantedesk')->__('View in DiamanteDesk'));
    }
}