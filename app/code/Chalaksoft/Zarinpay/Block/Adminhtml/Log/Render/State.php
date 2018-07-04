<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10/18/17
 * Time: 2:34 PM
 */
 namespace  Chalaksoft\Zarinpay\Block\Adminhtml\Log\Render;

use Magento\Framework\DataObject;

class State extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer

{

    public function render(DataObject $row)
    {

        if($row->getData('state')==0)return __("not accepted");
        return __("accepted");
    }
}