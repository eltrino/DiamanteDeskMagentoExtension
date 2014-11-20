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

class Eltrino_DiamanteDesk_Block_Adminhtml_Tickets_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('eltrino_diamantedesk');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $this->setForm($form);

        $fieldset = $form->addFieldset('new_ticket_form', array('legend' => $helper->__('Create new ticket')));

        $fieldset->addField('branch', 'select', array(
            'label' => $helper->__('Branch'),
            'required' => true,
            'name' => 'branch',
            'options' => Mage::getModel('eltrino_diamantedesk/source_branch')->toArray()
        ));

        $fieldset->addField('subject', 'text', array(
            'label' => $helper->__('Subject'),
            'required' => true,
            'name' => 'subject',
        ));

        $fieldset->addField('status', 'select', array(
            'label' => $helper->__('Status'),
            'required' => true,
            'name' => 'status',
            'options' => Mage::getModel('eltrino_diamantedesk/source_tickets_statuses')->getStatuses()
        ));

        $fieldset->addField('priority', 'select', array(
            'label' => $helper->__('Priority'),
            'required' => true,
            'name' => 'priority',
            'options' => Mage::getModel('eltrino_diamantedesk/source_tickets_priorities')->getPriorities()
        ));

        $fieldset->addField('source', 'select', array(
            'label' => $helper->__('Source'),
            'required' => true,
            'name' => 'source',
            'value' => 'web',
            'options' => Mage::getModel('eltrino_diamantedesk/source_tickets_sources')->getSources()
        ));

        $fieldset->addField('reporter', 'select', array(
            'label' => $helper->__('Reporter'),
            'required' => true,
            'name' => 'reporter',
            'options' => Mage::getModel('eltrino_diamantedesk/source_users')->getReporters(),
            'value' => Mage::getSingleton('admin/session')->getUser()->getEmail()
        ));

        $fieldset->addField('assignee', 'select', array(
            'label' => $helper->__('Assignee'),
            'name' => 'assignee',
            'options' => Mage::getModel('eltrino_diamantedesk/source_users')->getAssigners(),
            'value' => 0
        ));

        $fieldset->addField('description', 'editor', array(
            'label' => $helper->__('Description'),
            'required' => true,
            'name' => 'description'
        ));

        $form->setUseContainer(true);

        return parent::_prepareForm();
    }

}