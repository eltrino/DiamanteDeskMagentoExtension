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
class Eltrino_DiamanteDesk_Model_Source_Users extends Mage_Core_Model_Abstract
{
    /**
     * @var Eltrino_DiamanteDesk_Model_Api
     */
    protected static $_api;

    public function _construct()
    {
        parent::_construct();
        if (!static::$_api) {
            static::$_api = Mage::getSingleton('eltrino_diamantedesk/api');
        }
    }

    /**
     * @return array
     */
    public function getReporters()
    {
        $options = [];
        $diamanteUsers = $this->getDiamanteUsers();
        $oroUsers = $this->getOroUsers();

        foreach ($diamanteUsers as $user) {
            $firstName = isset($user->first_name) ? $user->first_name : false;
            $lastName  = isset($user->last_name)  ? $user->last_name  : false;
            if (!$firstName && !$lastName) {
                $options[Eltrino_DiamanteDesk_Model_Api::TYPE_DIAMANTE_USER . $user->id] =
                    $user->email . ' [diamante]';
            } else {
                $options[Eltrino_DiamanteDesk_Model_Api::TYPE_DIAMANTE_USER . $user->id] =
                    $user->first_name . ' ' . $user->last_name . ' - ' . $user->email . ' [diamante]';
            }
        }

        foreach ($oroUsers as $user) {
            $options[Eltrino_DiamanteDesk_Model_Api::TYPE_ORO_USER . $user->id] =
                $user->firstName . ' ' . $user->lastName . ' - ' . $user->email . ' [oro]';
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getAssigners()
    {
        $options = [];

        foreach ($this->getOroUsers() as $user) {
            $options[$user->id] =
                $user->firstName . ' ' . $user->lastName . ' - ' . $user->email;
        }

        return $options;
    }


    /**
     * @return mixed
     */
    private function getOroUsers()
    {
        return json_decode(static::$_api->addFilter('limit', '999999')->getUsers());
    }

    /**
     * @return array
     */
    private function getDiamanteUsers()
    {
        return static::$_api->getDiamanteUsers();
    }
}