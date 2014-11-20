<?php

/**
 * Class Eltrino_DiamanteDesk_Model_Resource_CustomerRelation
 */
class Eltrino_DiamanteDesk_Model_Resource_CustomerRelation extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('eltrino_diamantedesk/customer_relation', 'relation_id');
    }

}