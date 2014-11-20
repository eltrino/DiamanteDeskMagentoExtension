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

class Eltrino_DiamanteDesk_CustomerController extends Mage_Core_Controller_Front_Action
{
    const FIELD_NAME_SOURCE_FILE = 'attachment';

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        if ($block = $this->getLayout()->getBlock('diamantdesk.tickets')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    public function createTicketAction()
    {
        $this->loadLayout();
        if ($block = $this->getLayout()->getBlock('diamantdesk.createTicket')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    /**
     * Add ticket to DiamanteDesk,
     * If connection is not established, ticket will be added to queue,
     * And will be processed by cron
     */
    public function createTicketPostAction()
    {
        $post = $this->getRequest()->getPost();

        $description = strip_tags($post['description']);
        $description = preg_replace("/&#?[a-z0-9]+;/i", "", $description);
        $description = trim($description);
        if ($description == "") {
            Mage::getSingleton('core/session')->addError('Please fill the description field.');
            $this->_redirectReferer();
            return;
        }

        /** check order field */
        if (isset($post['order_id'])) {
            $incrementId = $post['order_id'];
        }
        $order = null;
        if (isset($incrementId) && !is_null($incrementId) && $incrementId != '') {
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
            'priority' => 'medium',
        );

        if ($order) {
            $data['order_increment_id'] = $incrementId;
        }

        try {

            $user = $api->getOrCreateDiamanteUser(Mage::getSingleton('customer/session')->getCustomer());

            if (!$user) {
                Mage::throwException('Can\'t connect to diamantedesk server');
            }

            $data['reporter'] = $user->id;

            if (!$api->createTicket($data)) {
                Mage::throwException('Can\'t connect to diamantedesk server');
                return;
            }

            if (isset($_FILES) && isset($_FILES[self::FIELD_NAME_SOURCE_FILE])) {
                $newTicket = json_decode($api->result->getBody());

                $fileContent = file_get_contents($_FILES[self::FIELD_NAME_SOURCE_FILE]['tmp_name']);
                $fileName = $_FILES[self::FIELD_NAME_SOURCE_FILE]['name'];

                $api->addAttachmentToTicket(array(
                    'ticket_id' => $newTicket->id,
                    'filename'  => $fileName,
                    'content'   => base64_encode($fileContent)
                ));

            }

        } catch (Exception $e) {
            $this->addToQueue($data);
            return;
        }

        Mage::getSingleton('core/session')
            ->addSuccess($this->__('Ticket was successfully created. Thank you!'));

        $this->_redirect('diamantedesk/customer');

    }

    public function addToQueue($data)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $session = Mage::getSingleton('core/session');
        if (!$customerId) {
            $session->addError($this->__('Something went wrong, please try again later'));
            $this->_redirect('*/*/index');
            return;
        }
        $queue = Mage::getModel('eltrino_diamantedesk/queue');
        $data['customer_id'] = $customerId;
        $data['created_at'] = Mage::getModel('core/date')->timestamp(time());
        $queue->addData($data);
        $queue->save();

        if ($queue->getId()) {
            $session->addSuccess($this->__("Ticket was successfully created. Thank you! It will be displayed in the list of tickets soon."));
        } else {
            $session->addError($this->__('Something went wrong, please try again later'));
        }

        $this->_redirectReferer();
    }

    public function viewAction()
    {
        $ticketKey = $this->getRequest()->getParam('key');

        if (!$ticketKey || !preg_match('~^[A-Z]+-\d+$~', $ticketKey)) {
            Mage::getSingleton('customer/session')->addError('Ticket not found');
            $this->_redirectReferer();
            return;
        }

        /** @var Eltrino_DiamanteDesk_Model_Api $api */
        $api = Mage::getSingleton('eltrino_diamantedesk/api');
        $ticket = $api->getTicket($ticketKey);

        $this->loadLayout();

        /** @var Eltrino_DiamanteDesk_Block_View $block */
        if ($block = $this->getLayout()->getBlock('diamantdesk.viewTicket')) {
            $block->setTicket($ticket);
        }
        $this->renderLayout();
    }

    /**
     * @throws Mage_Core_Exception
     */
    public function createCommentPostAction()
    {
        $post = $this->getRequest()->getPost();

        if (trim($post['comment']) === '') {
            Mage::getSingleton('core/session')->addError('Please fill the comment field.');
            $this->_redirectReferer();
            return;
        }

        $api = Mage::getSingleton('eltrino_diamantedesk/api');

        $user = $api->getOrCreateDiamanteUser(Mage::getSingleton('customer/session')->getCustomer());

        if (!$user) {
            Mage::throwException('Can\'t connect to diamantedesk server');
        }

        $data = array();
        $data['author'] = $user->id;
        $data['ticket'] = $post['ticket'];
        $data['content'] = $post['comment'];
        $data['ticketStatus'] = $post['status'];

        $api->createComment($data);

        $this->_redirectReferer();
    }
}