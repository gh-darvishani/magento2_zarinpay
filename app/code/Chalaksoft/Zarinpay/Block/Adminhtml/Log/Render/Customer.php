<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10/18/17
 * Time: 2:34 PM
 */
 namespace  Chalaksoft\Zarinpay\Block\Adminhtml\Log\Render;

use Magento\Framework\DataObject;

class Customer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $customerFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;

    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {

        $mageCateId = $row->getData('customer_id');
        $storeCat = $this->customerFactory->create()->load($mageCateId);
        return $storeCat->getName();
    }
}