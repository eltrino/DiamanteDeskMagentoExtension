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
class Eltrino_DiamanteDesk_Block_List extends Mage_Core_Block_Template
{
    /**
     * Cached customer tickets
     * @var Varien_Data_Collection
     */
    protected $_collection;

    /**
     * @var string
     */
    private $_serverAddress;

    /**
     * @var string
     */
    private $_frontendAddress;

    /**
     * @var Mage_Customer_Model_Customer
     */
    private $_customer;

    protected function _construct()
    {
        parent::_construct();
        /** Load Configuration */
        $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        $this->_serverAddress = trim(Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_API_SERVER_ADDRESS), '/');
        $this->_frontendAddress = trim(Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_CONFIGURATION_FOOTER_LINK_URL), '/');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $api = Mage::getSingleton('eltrino_diamantedesk/api');

        $diamanteUser = $api->getOrCreateDiamanteUser($this->_customer);

        if (!$diamanteUser) {
            return $this;
        }

        $this->getCollection()->addFieldToFilter(
            'reporter', array('eq' => Eltrino_DiamanteDesk_Model_Api::TYPE_DIAMANTE_USER . $diamanteUser->id)
        );

        $pager = $this->getLayout()->createBlock('page/html_pager', 'diamantedesk.tickets.pager')
            ->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }

    /**
     * Retrieve customer tickets
     * @return array
     */
    public function getCustomerTickets()
    {
        return $this->getCollection();
    }

    /**
     * @param $ticket Varien_Object
     * @return string
     */
    public function getTicketUrl($ticket)
    {
        if (!$ticket->getUrl()) {
            $ticket->setUrl($this->_frontendAddress . '/#tickets/' . $ticket->getKey());
        }
        return $ticket->getUrl();
    }

    public function getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = Mage::getModel('eltrino_diamantedesk/ticket')->getCollection();
        }

        return $this->_collection;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
    }
}