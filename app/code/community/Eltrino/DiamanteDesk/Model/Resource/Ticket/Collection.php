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
class Eltrino_DiamanteDesk_Model_Resource_Ticket_Collection extends Varien_Data_Collection
{
    const API_TOTAL_HEADER = 'X-total';

    const CREATED_AT_FIELD = 'createdAt';

    /** @var Eltrino_DiamanteDesk_Model_Api */
    protected $_api;

    /**
     * @var array
     */
    protected $_notApiFields = array(
        'email',
        'order_increment_id',
    );

    /** @var array */
    protected $_dateMap = array(
        'from' => 'createdAfter',
        'to' => 'createdBefore',
    );

    /**
     * @var bool|integer
     */
    protected $orderRelation = false;

    public function __construct()
    {
        parent::__construct();
        $this->_api = Mage::getSingleton('eltrino_diamantedesk/api');
    }

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $ticketsData = json_decode($this->_api->getTickets(false));

        if (!$ticketsData) {
            $this->_setIsLoaded();
            return $this;
        }

        $this->_totalRecords = $this->_api->result->getHeader(static::API_TOTAL_HEADER);

        $orderRelationCollection = Mage::getModel('eltrino_diamantedesk/orderRelation')->getCollection();

        $priorities = Mage::getModel('eltrino_diamantedesk/source_tickets_priorities')->getPriorities();
        $statuses = Mage::getModel('eltrino_diamantedesk/source_tickets_statuses')->getStatuses();

        foreach ($ticketsData as $ticketData) {

            if ($this->orderRelation) {
                $relation = $orderRelationCollection->getItemsByColumnValue('order_increment_id', $this->orderRelation);
                if (count($relation)) {
                    $isCurrentTicket = false;
                    foreach ($relation as $relationItem) {
                        if ($ticketData->id == $relationItem->getTicketId()){
                            $isCurrentTicket = true;
                            break;
                        }
                    }

                    if (!$isCurrentTicket) {
                        continue;
                    }
                }
            }

            $relatedOrders = $orderRelationCollection->getItemsByColumnValue('ticket_id', $ticketData->id);

            $orderIncrementId = null;
            if (count($relatedOrders)) {
                $orderIncrementId = $relatedOrders[0]->getOrderIncrementId();
            }

            $ticket = Mage::getModel('eltrino_diamantedesk/ticket');
            $ticket->addData(array(
                'id' => $ticketData->id,
                'subject' => Mage::helper('catalog/output')->escapeHtml($ticketData->subject),
                'email' => '',
                'createdAt' => $ticketData->created_at,
                'priority' => $priorities[$ticketData->priority],
                'status' => $statuses[$ticketData->status],
                'order_increment_id' => $orderIncrementId,
                'reporter' => $ticketData->reporter,
                'key' => $ticketData->key
            ));
            $this->addItem($ticket);
        }

        $this->_setIsLoaded();

        return $this;

    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == static::CREATED_AT_FIELD) {
            $keysDateMap = array_keys($this->_dateMap);
            foreach ($condition as $key => $property) {
                if (in_array($key, $keysDateMap) && $property instanceof Zend_Date) {
                    $this->_api->addFilter($this->_dateMap[$key], $property->getIso());
                }
            }
        } else {
            foreach ($condition as $key => $value) {
                // (string)$value - for converting a Zend_Db_Expr to string value
                $this->_api->addFilter($field, trim(trim((string)$value, '\''), '%'));
            }
        }

        return $this;
    }

    /**
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        if (in_array($attribute, $this->_notApiFields)) {
            return $this;
        }

        $this->_api
            ->addFilter('sort', $attribute)
            ->addFilter('order', $dir);

        return $this;
    }

    /**
     * Set collection page size
     *
     * @param   int $size
     * @return  Varien_Data_Collection
     */
    public function setPageSize($size)
    {
        $this->_pageSize = $size;
        $this->_api->addFilter('limit', $size);
        return $this;
    }

    /**
     * Set current page
     *
     * @param   int $page
     * @return  Varien_Data_Collection
     */
    public function setCurPage($page)
    {
        $this->_curPage = $page;
        $this->_api->addFilter('page', $page);
        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->_totalRecords;
    }

    public function setLimitedByOrderRelation($incrementId)
    {
        $this->orderRelation = $incrementId;
    }
}