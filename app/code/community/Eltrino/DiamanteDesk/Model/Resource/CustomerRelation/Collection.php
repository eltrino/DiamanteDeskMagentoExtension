<?php

/**
 * Class Eltrino_DiamanteDesk_Model_Resource_CustomerRelation_Collection
 */
class Eltrino_DiamanteDesk_Model_Resource_CustomerRelation_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('eltrino_diamantedesk/customerRelation');
    }

}