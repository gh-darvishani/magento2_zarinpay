<?php
/**
 * Created by PhpStorm.
 * User: chiya
 * Date: 4/12/18
 * Time: 11:05 PM
 */

namespace Chalaksoft\Zarinpay\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface {

    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;

        $installer->startSetup();
       $table_name= $installer->getTable("zarinpay_log");

        if(version_compare($context->getVersion(), '1.0.1', '<')) {
            $installer->run("ALTER TABLE ".$table_name." CONVERT TO CHARACTER SET utf8;
");
        }

        $installer->endSetup();
    }
}

?>