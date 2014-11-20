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
class Eltrino_DiamanteDesk_Model_Ticket extends Mage_Core_Model_Abstract
{
    /** @var string */
    protected $_resourceName = 'eltrino_diamantedesk/ticket';
    /** @var string */
    protected $_resourceCollectionName = 'eltrino_diamantedesk/ticket_collection';
    /** @var string */
    protected $_idFieldName = 'ticket_id';

    public function getId()
    {
        return $this->getData('id');
    }
}