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

class Eltrino_DiamanteDesk_Model_Source_Tickets_Statuses extends Mage_Core_Model_Abstract
{
    /** @var array */
    protected $_defaultStatuses = array(
        'new' => 'New',
        'open' => 'Open',
        'pending' => 'Pending',
        'in_progress' => 'In progress',
        'closed' => 'Closed',
        'on_hold' => 'On hold',
    );

    public function getStatuses()
    {
//        $tickets = json_decode(Mage::getSingleton('eltrino_diamantedesk/api')->getTickets());
//        if (count($tickets) && isset($tickets[0])) {
//            return (array)$tickets[0]->status->value_to_label_map;
//        }
        return $this->_defaultStatuses;
    }
}