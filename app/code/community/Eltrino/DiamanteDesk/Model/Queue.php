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

class Eltrino_DiamanteDesk_Model_Queue extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('eltrino_diamantedesk/queue');
    }

    protected function _getExportableTickets()
    {
        return $this->getCollection()
            ->addFieldToFilter('is_exported', false);
    }

    public function processQueue()
    {
        $api = Mage::getSingleton('eltrino_diamantedesk/api');

        $user = $api->getUserByFilter('username', Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_API_USERNAME));

        foreach ($this->_getExportableTickets() as $ticket) {

            $data = array(
                'subject' => $ticket->getSubject() . "\n",
                'description' => $ticket->getDescription() . "\n",
                'reporter' => $user->id,
                'branch' => $ticket->getBranch() ? $ticket->getBranch() : 1,
                'source' => 'web',
                'status' => 'new',
                'priority' => 'medium',
                'order_increment_id' => $ticket->getOrderIncrementId(),
            );

            try {
                if ($api->createTicket($data)) {
                    $this->updateTicket($ticket, true, $api->result);
                } else {
                    $this->updateTicket($ticket, false, $api->result);
                }
            } catch (Exception $e) {
                $this->updateTicket($ticket, false, $api->result);
            }
        }
    }

    public function updateTicket($ticket, $isSuccess, $response)
    {
        $ticket->setExportedAt(Mage::getModel('core/date')->timestamp(time()))
            ->setIsExported($isSuccess)
            ->setResponse($response->getStatus())
            ->save();
    }

    public function checkQueue()
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('response', array('notnull' => true))
            ->addFieldToFilter('response', array('neq' => 201));

        if ($collection->getSize()) {
            $ids = $collection->getAllIds();
            $helper = Mage::helper('eltrino_diamantedesk');
            Mage::getModel('adminnotification/inbox')->addCritical(
                $helper->__('Problem with Diamantedesk module'),
                $helper->__(sprintf('Can\'t export tickets with ids %s to Diamantedesk', implode(', ', $ids)))
            );
        }
    }

}