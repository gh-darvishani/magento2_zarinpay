<?php
namespace Chalaksoft\Zarinpay\Block;

use Exception;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;
use Chalaksoft\Zarinpay\Model\LogFactory;

/**
 * Class Redirect
 * @package Chalaksoft\Zarinpay\Block
 *  =>t id
 * =>username
 * =>password
 */
class Redirect extends \Magento\Framework\View\Element\Template
{

    protected $_checkoutSession;
    protected $_orderFactory;
    protected $_scopeConfig;
    protected $_urlBuilder;
    protected $messageManager;
    protected $redirectFactory;
    protected $catalogSession;
    protected $zarinpay_log;
    protected $customer_session;

    /**
     * @var $order Order
     */
    protected $order;
    protected $response;

  private $namespace='http://interfaces.core.sw.bps.com/';
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
          \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
    LogFactory $zarinpay_log,
    Session $customer_session,
    RedirectFactory $redirectFactory,
        \Magento\Framework\App\Response\Http $response,

        Template\Context $context,
        array $data
    )
    {
        $this->customer_session=$customer_session;
        $this->zarinpay_log =$zarinpay_log;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_urlBuilder=$context->getUrlBuilder();
        $this->messageManager=$messageManager;
        $this->redirectFactory=$redirectFactory;
        $this->response = $response;

        parent::__construct($context, $data);

    }
    protected $sendToBankUrl="https://www.zarinpal.com/pg/rest/WebGate/PaymentRequest.json";
//    protected $merchantID="be7959f8-645b-11e6-836e-000c295eb8fc";
    public function sendToBank()
    {

        if (!$this->getOrderId()) {
            $this->response->setRedirect($this->_urlBuilder->getUrl(''));

            return "";
        }
        $response['state']=true;
        $response['msg']="";
        $data = array('MerchantID' => $this->getMerchantID(),
            'Amount' => $this->getOrderPrice(),
            'CallbackURL' => $this->getCallBackUrl(),
            'Description' => $this->getDescription());
        $jsonData = json_encode($data);
        $ch = curl_init($this->sendToBankUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true);

        curl_close($ch);
        if ($err) {
            $response['msg']= "cURL Error #:" . $err;
            $response['state']=false;
        } else {
            if ($result["Status"] == 100) {
                $response['state']=true;
                $response['msg']=$this->getBankUrl($result["Authority"]);
            } else {
                $response['msg']=  $this->error($result["Status"]);
                $response['state']=false;
            }
        }
        //create log
        if($response['state']){
            $msg="customer send to bank with key # ".$result["Authority"];
            $this->changeStatus($this->getBeforeOrderStatus());
        }else{
            $this->changeStatus(Order::STATE_CANCELED);
            $msg=$response['msg'];
        }
        $log_data=[
            'state'=>0,
            'customer_id'=>$this->customer_session->getCustomer()->getId(),
            'order_id'=>$this->getOrderId(),
            'time_create'=>time(),
            'amount'=>$this->getOrderPrice(),
            'message'=>$msg
        ];
        $this->saveLog($log_data);
        return $response;

    }

    public function saveLog(array $data){
        $this->zarinpay_log->create()->addData($data)->save();
    }

    public function updateLog(array $data){


        $log=$this->zarinpay_log->create()->getCollection()
            ->addFieldToFilter("order_id",$data['order_id'])
            ->addFieldToFilter("state",0)
        ->getFirstItem();
        if(!$log->getData())
            return;
        $updated_log=$this->zarinpay_log->create()->load($log->getId());
            $updated_log->setData($data)
                ->setId($log->getId())
            ->save();
    }




    public function getFormData($paramter)
    {
        return $this->getConfig($paramter);
    }
    public function getOrderPrice(){


        $extra=10;
        if($this->useToman()){
            $extra=1;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder();
        $amount=$order->getGrandTotal()/$extra;
        $extra_amount=$amount*$this->getExtraAmount()/100;
        return  (int) $amount+(int)$extra_amount;
        }

    private function getOrder(){

       return $this->_orderFactory->create()->load($this->getOrderId());
    }

    function changeStatus($status){
        $order=$this->getOrder();
        $order->setStatus($status);
        $order->save();
    }
    public function getOrderId(){

        return isset($_COOKIE['order_id'])?$_COOKIE['order_id']:false;
    }

    public function getCallBackUrl(){
        return $this->_urlBuilder->getUrl('checkout/onepage/success');
    }



    public function countPrice($order_item){
        $price=0;
        foreach ($order_item as $_item) {
                $price+=$_item->getPrice();
        }
        return $price;
    }



    private function getConfig($value){
        return $this->_scopeConfig->getValue('payment/zarinpay/'.$value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getDescription()
    {
        return $this->getConfig('description');
    }
    public function getExtraAmount()
    {
        return $this->getConfig('extra');
    }
  public function getMerchantID()
    {
        return $this->getConfig('merchant_id');
    }
  public function getBankUrl($auth)
    {
        return str_replace("%s",$auth,$this->getConfig('url'));
    }


    public function getBeforeOrderStatus()
    {
        return $this->getConfig('order_status');
    }
    public function getAfterOrderStatus()
    {
        return $this->getConfig('after_order_status');
    }
    public function useToman()
    {
        return $this->getConfig('isirt');
    }


    public function verifySettleTransaction(){
        $data=$this->getRequest()->getParams();
        $order=$this->getOrder();

        $customer_id=$this->customer_session->getCustomerId();

        //check for hacked =>if we have log with state
        $response['state']=false;
        $response['msg']="";

        if(!$order->getData())
        {
            $response['msg']="این تراکنش قبلا اعتبار سنجی شده است.";

        }else {
            $Authority = $data['Authority'];
            $data = array('MerchantID' => $this->getMerchantID(), 'Authority' => $Authority, 'Amount' => $this->getOrderPrice());
            $jsonData = json_encode($data);
            $ch = curl_init('https://www.zarinpal.com/pg/rest/WebGate/PaymentVerification.json');
            curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ));
            $result = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            $result = json_decode($result, true);
            if ($err) {
                $response['state'] = false;
                $response['msg'] = __("cURL Error #: %1", $err);
            } else {
                if ($result['Status'] == 100) {
                    $response['state'] = true;
                    $response['msg'] = __('تراکنش با موفقیت ثبت شد . شماره تراکنش : %1', $result['RefID']);
                } else {
                    $response['state'] = false;
                    $response['msg'] = __('خطایی رخ داده است . خطا : %1', $this->error($result['Status']));
                }
            }

            if ($response['state']) {
                $this->changeStatus($this->getAfterOrderStatus());
            } else {
                $this->changeStatus(Order::STATE_CANCELED);
            }
            $log_data = [
                'state' => $response['state'],
                'customer_id' => $customer_id,
                'order_id' => $this->getOrderId(),
                'amount' => $this->getOrderPrice(),
                'message' => $response['msg']
            ];
            $this->updateLog($log_data);
            //unset order id
            $this->removeOrderId();
        }
        return $response;
    }

    protected function error($key){
        $arr=
            [
                "-1"=>"اطلاعات ارسال شده ناقص است.",
                "-2"=>" IP و يا مرچنت كد پذيرنده صحيح نيست",
                "-3"=>"با توجه به محدوديت هاي شاپرك امكان پرداخت با رقم درخواست شده ميسر نمي باشد.",
                "-4"=>"سطح تاييد پذيرنده پايين تر از سطح نقره اي است.",
                "-11"=>"درخواست مورد نظر يافت نشد.￼",
                "-12"=>"امكان ويرايش درخواست ميسر نمي باشد.",
                "-21"=>"هيچ نوع عمليات مالي براي اين تراكنش يافت نشد.",
                "-22"=>"تراكنش نا موفق ميباشد.",
                "-33"=>"رقم تراكنش با رقم پرداخت شده مطابقت ندارد.",
                "-34"=>"سقف تقسيم تراكنش از لحاظ تعداد يا رقم عبور نموده است",
                "-40"=>"اجازه دسترسي به متد مربوطه وجود ندارد.",
                "-41"=>"اطلاعات ارسال شده مربوط به AdditionalData غيرمعتبر ميباشد",
                "-42"=>"مدت زمان معتبر طول عمر شناسه پرداخت بايد بين 30 دقيه تا 45 روز مي باشد.",
                "-54"=>"درخواست مورد نظر آرشيو شده است.",
                "100"=>"عمليات با موفقيت انجام گرديده است.",
                "101"=>"عمليات پرداخت موفق بوده و قبلا PaymentVerification تراكنش انجام شده است.",

            ];
        return $arr[$key];
    }
    function removeOrderId()
    {
        setcookie("order_id", "", time() - 3600,"/");
    }
}

