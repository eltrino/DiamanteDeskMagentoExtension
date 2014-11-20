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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Order Relation table
$table = $installer->getConnection()
    ->newTable($installer->getTable('eltrino_diamantedesk/order_relation'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'Id')
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Magento order increment id')
    ->addColumn('ticket_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'DiamanteDesk ticket id');

$installer->getConnection()->createTable($table);


// Add order relation column to queue table
$installer->getConnection()->addColumn(
    $this->getTable('eltrino_diamantedesk/queue'), 'order_increment_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned' => true,
        'comment' => 'Magento order increment id',
    )
);

$installer->endSetup();
