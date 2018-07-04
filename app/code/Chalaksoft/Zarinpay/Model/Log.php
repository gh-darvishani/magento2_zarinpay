<?php
namespace Chalaksoft\Zarinpay\Model;

class Log extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Chalaksoft\Zarinpay\Model\ResourceModel\Log');
    }
}
?>