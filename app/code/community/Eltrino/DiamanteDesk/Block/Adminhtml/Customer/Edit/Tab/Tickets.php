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

class Eltrino_DiamanteDesk_Block_Adminhtml_Customer_Edit_Tab_Tickets
    extends Eltrino_DiamanteDesk_Block_Adminhtml_Tickets_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected $_columnsToRemove = array('email');

    protected $_reporter;

    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_edit_tab_tickets');
        $this->setUseAjax(true);
    }

    public function getTabLabel()
    {
        return $this->__('Tickets');
    }

    public function getTabTitle()
    {
        return $this->__('Tickets');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getGridUrl($params = array())
    {
        return $this->getUrl('*/diamantedesk/customerTicketsGrid', array('_current'=>true));
    }

    protected function _prepareCollection()
    {
        /** @var Eltrino_DiamanteDesk_Model_Resource_Ticket_Collection $collection */
        $collection = Mage::getModel('eltrino_diamantedesk/ticket')->getCollection();

        $collection->addFieldToFilter(
            'reporter',
            array(
                'eq' => $this->getReporter(Mage::registry('current_customer'))
            )
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        foreach ($this->_columns as $key => $value) {
            if (in_array($key, $this->_columnsToRemove)) {
                unset($this->_columns[$key]);
            }
        }
        return $result;
    }

    /**
     * @param $customer
     * @return string
     */
    private function getReporter(Mage_Customer_Model_Customer $customer)
    {
        if ($this->_reporter) {
            return $this->_reporter;
        }
        $model = Mage::getModel('eltrino_diamantedesk/customerRelation')->load($customer->getId(), 'customer_id');
        if ($model->getId()) {
            return Eltrino_DiamanteDesk_Model_Api::TYPE_DIAMANTE_USER . $model->getUserId();
        }

        return '';
    }
}
