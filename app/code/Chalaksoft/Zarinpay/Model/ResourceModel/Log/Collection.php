<?php

namespace Chalaksoft\Zarinpay\Model\ResourceModel\Log;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Chalaksoft\Zarinpay\Model\Log', 'Chalaksoft\Zarinpay\Model\ResourceModel\Log');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>