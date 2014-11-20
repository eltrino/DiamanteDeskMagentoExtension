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
class Eltrino_DiamanteDesk_Block_Adminhtml_Tickets_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
        if ($this->getCollection()) {
            return parent::_prepareCollection();
        }

        /** @var Varien_Data_Collection $collection */
        $collection = Mage::getModel('eltrino_diamantedesk/ticket')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $helper = Mage::helper('eltrino_diamantedesk');

        $this->addColumn('id', array(
            'header' => $helper->__('Ticket ID'),
            'index' => 'id',
            'width' => '50px',
            'type' => 'number',
            'filter' => false,
            'column_css_class'=>'no-display',
            'header_css_class'=>'no-display',
        ));

        $this->addColumn('key', array(
            'header' => $helper->__('Key'),
            'index' => 'key',
            'type' => 'text',
            'filter' => false
        ));

        $this->addColumn('subject', array(
            'header' => $helper->__('Subject'),
            'index' => 'subject',
        ));

        $this->addColumn('email', array(
            'header' => $helper->__('Email'),
            'index' => 'email',
            'type' => 'text',
            'renderer' => 'eltrino_diamantedesk/adminhtml_tickets_grid_renderer_email',
            'filter' => false,
        ));

        $this->addColumn('reporter', array(
            'header' => $helper->__('Reporter'),
            'index' => 'reporter',
            'type' => 'text',
            'filter' => false,
            'column_css_class'=>'no-display',
            'header_css_class'=>'no-display',
        ));

        $this->addColumn('priority', array(
            'header' => $helper->__('Priority'),
            'index' => 'priority',
            'type' => 'options',
            'options' => Mage::getModel('eltrino_diamantedesk/source_tickets_priorities')->getPriorities(),
            'width' => '150px',
        ));

        $this->addColumn('status', array(
            'header' => $helper->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('eltrino_diamantedesk/source_tickets_statuses')->getStatuses(),
            'width' => '150px',
        ));


        $this->addColumn('createdAt', array(
            'header' => $helper->__('Date'),
            'index' => 'createdAt',
            'type' => 'datetime',
            'width' => '200px',
        ));

        $this->addColumn('order_increment_id', array(
            'header' => $helper->__('Order ID'),
            'index' => 'order_increment_id',
            'type' => 'number',
            'width' => '50px',
            'filter' => false,
        ));


        $this->addColumn('link', array(
            'header' => $helper->__('View in DiamanteDesk'),
            'index' => 'link',
            'renderer' => 'eltrino_diamantedesk/adminhtml_tickets_grid_renderer_link',
            'filter' => false,
            'sortable' => false,
            'width' => '50px'
        ));

        return parent::_prepareColumns();
    }

}