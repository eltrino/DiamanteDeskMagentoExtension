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

class Eltrino_DiamanteDesk_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_DIAMANTE_DESK_API_USERNAME = 'diamantedesk/api/username';
    const XML_PATH_DIAMANTE_DESK_API_KEY = 'diamantedesk/api/key';
    const XML_PATH_DIAMANTE_DESK_API_SERVER_ADDRESS = 'diamantedesk/api/server_address';
    const XML_PATH_DIAMANTE_DESK_BRANCH_CONFIGURATION_BRANCH = 'diamantedesk/branch_configuration/branch';
    const XML_PATH_DIAMANTE_DESK_CONFIGURATION_FOOTER_LINK_URL = 'diamantedesk/configuration/footer_link_url';

    public function getSupportLink()
    {
        return Mage::getStoreConfig(static::XML_PATH_DIAMANTE_DESK_CONFIGURATION_FOOTER_LINK_URL);
    }
}