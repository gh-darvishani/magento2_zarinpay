<?php
namespace Chalaksoft\Zarinpay\Block\Adminhtml\Log;

use Chalaksoft\Zarinpay\Block\Adminhtml\Log\Render\Customer;
use Chalaksoft\Zarinpay\Block\Adminhtml\Log\Render\Order;
use Chalaksoft\Zarinpay\Block\Adminhtml\Log\Render\State;
use Chalaksoft\Zarinpay\Block\Adminhtml\Log\Render\Time;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Chalaksoft\Zarinpay\Model\logFactory
     */
    protected $_logFactory;

    /**
     * @var \Chalaksoft\Zarinpay\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Chalaksoft\Zarinpay\Model\logFactory $logFactory
     * @param \Chalaksoft\Zarinpay\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Chalaksoft\Zarinpay\Model\LogFactory $LogFactory,
        \Chalaksoft\Zarinpay\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_logFactory = $LogFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_logFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'state',
					[
						'header' => __('State'),
						'index' => 'state',
                        'renderer'=>State::class
					]
				);
				
				$this->addColumn(
					'time_create',
					[
						'header' => __('Create At'),
						'index' => 'time_create',
                        'renderer'=>Time::class
					]
				);
				
				$this->addColumn(
					'message',
					[
						'header' => __('Message'),
						'index' => 'message',
					]
				);
				
				$this->addColumn(
					'code',
					[
						'header' => __('Code'),
						'index' => 'code',
					]
				);
				
				$this->addColumn(
					'amount',
					[
						'header' => __('Amount'),
						'index' => 'amount',
                        'type'=>"price"
					]
				);
				
				$this->addColumn(
					'customer_id',
					[
						'header' => __('Customer'),
						'index' => 'customer_id',
                        'renderer'=>Customer::class
					]
				);
				
				$this->addColumn(
					'order_id',
					[
						'header' => __('Order'),
						'index' => 'order_id',
                        'renderer'=>Order::class
					]
				);
				


		
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('zarinpay/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('zarinpay/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Chalaksoft_Zarinpay::log/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('log');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('zarinpay/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('zarinpay/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('zarinpay/*/index', ['_current' => true]);
    }

    /**
     * @param \Chalaksoft\Zarinpay\Model\log|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'zarinpay/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	

}