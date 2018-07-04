<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10/18/17
 * Time: 2:34 PM
 */
 namespace  Chalaksoft\Zarinpay\Block\Adminhtml\Log\Render;

use Magento\Framework\DataObject;

class Time extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer

{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $orderFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @internal param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
        
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        if(!$row->getData('time_create'))
            return "";
//        return date("Y/m/d H:i:s",time());
        return date("Y/m/d H:i:s",$row->getData('time_create'));
        $mageCateId = $row->getData('order_id');
        $storeCat = $this->orderFactory->create()->load($mageCateId);
        return $storeCat->getIncrementId();
    }
}