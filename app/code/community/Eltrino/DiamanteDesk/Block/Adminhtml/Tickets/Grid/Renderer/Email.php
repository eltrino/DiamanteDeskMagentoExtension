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

class Eltrino_DiamanteDesk_Block_Adminhtml_Tickets_Grid_Renderer_Email extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @var array
     */
    private static $diamanteUsers;

    public function render(Varien_Object $row)
    {
        $email = $this->getEmail($row);
        $customer = Mage::getModel('customer/customer')
            ->getCollection()
            ->addFieldToFilter('email', $email)
            ->getFirstItem();

        if ($customer->getId()) {
            return sprintf('<a href="%s" target="_blank">%s</a>', Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $customer->getId())), $email);
        }
        return $row->getEmail();
    }

    /**
     * @param Varien_Object $row
     * @return string
     */
    private function getEmail(Varien_Object $row) {

        $users = $this->getDiamanteUsers();
        foreach ($users as $user)
        {
            if ($user->id == $this->extractReporterId($row->getReporter())) {
                return $user->email;
            }
        }
        return '';
    }

    /**
     * @return array
     */
    private function getDiamanteUsers()
    {
        if (!static::$diamanteUsers) {
            static::$diamanteUsers = Mage::getModel('eltrino_diamantedesk/api')->getDiamanteUsers();
        }
        return static::$diamanteUsers;
    }

    /**
     * @param $reporter
     * @return int|null
     */
    private function extractReporterId($reporter)
    {
        if (strpos($reporter, 'oro_')) {
            return null;
        }

        if (strpos($reporter, '_')) {
            $parts = explode('_',$reporter);
            return $parts[1];
        }
        return $reporter;
    }
}