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
class Eltrino_DiamanteDesk_Block_View extends Mage_Core_Block_Template
{
    protected $_ticket;

    /**
     * @var Mage_Customer_Model_Customer
     */
    private $_customer;

    /**
     * @return mixed
     */
    public function getTicket()
    {
        if (isset($this->_ticket)) {
            if (is_array($this->_ticket->comments)) {
                foreach ($this->_ticket->comments as $key => $comment) {
                    if ($comment->private) {
                        unset($this->_ticket->comments[$key]);
                    }
                }
            }
        }
        return $this->_ticket;
    }

    /**
     * @param mixed $ticket
     * @return $this
     */
    public function setTicket($ticket)
    {
        $this->_ticket = $ticket;
        $this->getLayout()->getBlock('head')->setTitle('View Ticket: ' . $ticket->key);
        return $this;
    }

    protected function _construct()
    {
        parent::_construct();
        /** Load Configuration */
        $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('eltrino/diamantedesk/list');
    }

    public function getCreateCommentUrl()
    {
        return $this->getUrl('diamantedesk/customer/createCommentPost');
    }

    public function getAlias()
    {
        return $this->_alias;
    }

}