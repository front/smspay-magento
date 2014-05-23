<?php

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `phone_code` VARCHAR( 10 ) NULL COMMENT 'SmsPay Phone Code';
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `phone_number` VARCHAR( 50 ) NULL COMMENT 'SmsPay Phone Number';
    
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `phone_code` VARCHAR( 10 ) NULL COMMENT 'SmsPay Phone Code';
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `phone_number` VARCHAR( 50 ) NULL COMMENT 'SmsPay Phone Number';
");

$installer->endSetup();