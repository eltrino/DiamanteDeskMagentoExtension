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

class Eltrino_DiamanteDesk_Adminhtml_DiamantedeskController extends Mage_Adminhtml_Controller_Action
{
    public function checkConnectionAction()
    {
        if (!$this->getRequest()->isAjax()) {
            return;
        }

        $post = $this->getRequest()->getPost();

        $this->getResponse()->setBody(
            Mage::getSingleton('eltrino_diamantedesk/api')
                ->initConfig($post['user_name'], $post['api_key'], $post['server_address'])
                ->getBranches()
        );

    }

    public function ticketsAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('diamantedesk');
        $contentBlock = $this->getLayout()->createBlock('eltrino_diamantedesk/adminhtml_tickets');
        $siteSelectorBlock = $this->getLayout()->createBlock('adminhtml/store_switcher');
        $this->_addContent($siteSelectorBlock);
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }

    public function indexAction()
    {
        $this->ticketsAction();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('diamantedesk');
        $this->_addContent($this->getLayout()->createBlock('eltrino_diamantedesk/adminhtml_tickets_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        try {
            Mage::getSingleton('eltrino_diamantedesk/api')->createTicket($this->getRequest()->getParams());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Your ticket has been successfully created'));
        $this->_redirect('adminhtml/diamantedesk/tickets');
    }

    public function simpleFormSubmitAction()
    {
        $post = $this->getRequest()->getPost();

        /** check order field */
        $incrementId = $post['order_id'];
        $order = null;
        if (isset($incrementId) && !is_null($incrementId)) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
            if (!$order->getId()) {
                Mage::getSingleton('core/session')->addError($this->__('Order does not exists'));
                $this->_redirectReferer();
                return;
            }
        }

        /** @var Eltrino_DiamanteDesk_Model_Api $api */
        $api = Mage::getSingleton('eltrino_diamantedesk/api');

        $branch = isset($post['branch']) ? $post['branch'] : Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_BRANCH_CONFIGURATION_BRANCH);

        $data = array(
            'subject' => $post['subject'],
            'description' => $post['description'],
            'branch' => (int)$branch,
            'source' => 'web',
            'status' => 'new',
            'priority' => 'low',
        );

        if ($order) {
            $data['order_increment_id'] = $incrementId;
        }

        try {

            $user = $api->getUserByFilter('username', Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_API_USERNAME));

            if (!$user || $user->status_code == 500) {
                Mage::throwException('Can\'t connect to diamantedesk server');
            }

            $data['reporter'] = $user->id;

            if (!$api->createTicket($data)) {
                Mage::throwException('Can\'t connect to diamantedesk server');
                return;
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        Mage::getSingleton('core/session')
            ->addSuccess($this->__('Ticket was successfully created. Thank you!'));

        $this->_redirectReferer();
    }

    public function orderTicketsGridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('eltrino_diamantedesk/adminhtml_sales_order_edit_tab_tickets')->toHtml()
        );
    }

    public function customerTicketsGridAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);

        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('eltrino_diamantedesk/adminhtml_customer_edit_tab_tickets')->toHtml()
        );
    }
}