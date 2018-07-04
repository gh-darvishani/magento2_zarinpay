<?php

namespace Chalaksoft\Zarinpay\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0){

		$installer->run('CREATE TABLE `zarinpay_log` ( `id` INT(11) NOT NULL AUTO_INCREMENT , 
`customer_id` INT(11) NOT NULL , `order_id` INT(11) NOT NULL , 
`state` INT(11) NOT NULL , 
`time_create` VARCHAR(1000) NOT NULL , 
`message` VARCHAR(1000) NOT NULL , 
`amount` VARCHAR(1000) NOT NULL , `code` VARCHAR(2000) NOT NULL , INDEX (`id`)) ENGINE = InnoDB');


		//demo 
//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//$scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
//demo 

		}

        $installer->endSetup();

    }
}