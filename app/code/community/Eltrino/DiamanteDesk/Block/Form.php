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

class Eltrino_DiamanteDesk_Block_Form extends Mage_Core_Block_Template
{

    public $showTitle = true;

    public $showBackLink = true;

    public function getSaveUrl()
    {
        return $this->getUrl('diamantedesk/customer/createTicketPost');
    }

    public function getNameBlockHtml()
    {
        /** @var Mage_Customer_Block_Widget_Name $nameBlock */
        $nameBlock = $this->getLayout()
            ->createBlock('customer/widget_name');

        $user = Mage::getSingleton('eltrino_diamantedesk/api')->getUserByFilter(
            'email', Mage::getSingleton('customer/session')->getCustomer()->getEmail()
        );

        if ($user) {
            $userData = new Varien_Object(
                array(
                    'firstname' => $user->firstName,
                    'lastname' => $user->lastName,
                )
            );
            $nameBlock->setObject($userData);

            /** disable editing to prevent conflict */

            $nameBlock->setFieldParams('disabled="disabled"');

        } else {
            $nameBlock->setObject(Mage::getSingleton('customer/session')->getCustomer());
        }

        Mage::getSingleton('eltrino_diamantedesk/api')->getUserByEmail(
            'email', Mage::getSingleton('customer/session')->getCustomer()->getEmail()
        );

        return $nameBlock->toHtml();
    }

    public function getBranches()
    {
        if (Mage::getStoreConfig(Eltrino_DiamanteDesk_Helper_Data::XML_PATH_DIAMANTE_DESK_BRANCH_CONFIGURATION_BRANCH) == 0) {
            if ($branches = Mage::getSingleton('eltrino_diamantedesk/api')->getBranches()) {
                return json_decode($branches);
            }
        }

        return array();
    }

    public function getOrderIncrementId()
    {
        if ($order = Mage::registry('current_order')) {
            return $order->getIncrementId();
        }

        return false;
    }

    public function setShowBackLink($value)
    {
        $this->showBackLink = (bool)$value;
        return $this;
    }

    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
    }

    /**
     * @return bool|Mage_Sales_Model_Resource_Order_Collection
     */
    public function getOrderCollection()
    {
        $session = Mage::getSingleton('customer/session');
        $customer = $session->getCustomer();

        if (!$customer) {
            return false;
        }

        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('customer_id', array('eq', $customer->getId()));

        if (!$orderCollection->getSize()) {
            return false;
        }

        return $orderCollection;

    }

}