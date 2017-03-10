<?php
/**
* bbc系统接口请求实现
*
* @category apibusiness
* @package apibusiness/lib/request/v1
* @author chenping<chenping@shopex.cn>
* @version $Id: 485.php 2013-13-12 14:44Z
*/
class apibusiness_request_v1_shopex_bbc extends apibusiness_request_shopexabstract
{
    public function __construct()
    {
        parent::__construct();

        $this->_caller->set_matrix_v('2.0');
    }

    public function add_delivery($delivery)
    {
        $rs = array('rsp'=>'fail','msg'=>'','data'=>'');
        if (!$delivery) {
            $rs['msg'] = 'no delivery';
            return $rs;
        }
        if ($delivery['process'] != 'true') {
            $rs['msg'] = 'no ship';
            return $rs;
        }


        $deliOrderModel = app::get(self::_APP_NAME)->model('delivery_order');
        $orderModel = app::get(self::_APP_NAME)->model('orders');
        if ($delivery['is_bind'] == 'true') {
            $deliOrderList = $deliOrderModel->getList('*',array('delivery_id'=>$delivery['delivery_id']));
            if ($deliOrderList) {
                foreach ($deliOrderList as $key => $deliOrder) {
                    $order = $orderModel->dump(array('order_id'=>$deliOrder['order_id']),'ship_status,shop_id,order_bn,is_delivery,mark_text,sync,order_id,self_delivery,createway,logi_id');

                    //ExBOY加入部分发货时也回写
                    if ($order['ship_status'] != '1' && $order['ship_status'] != '2') {
                        continue;
                    }
                    
                    if ($delivery['shop_id'] != $order['shop_id']) {
                        $mydelivery = $deliOrderModel->dump(array('order_id' => $deliOrder['order_id'],'delivery_id|noequal'=>$delivery['delivery_id']));
                        if ($mydelivery) {
                            kernel::single('ome_service_delivery')->delivery($mydelivery['delivery_id']);
                        }
                        continue;
                    }

                    $delivery['order'] = $order;
  
                    $this->delivery_request($delivery);
                    
                }
            }
        } else {
            
            if( !isset($delivery['delivery_id']) ){
                $deliOrder['order_id'] = $delivery['order']['order_id'];
            }else{
                $deliOrder = $deliOrderModel->dump(array('delivery_id'=>$delivery['delivery_id']),'*');
            }
            
            $order = $orderModel->dump(array('order_id'=>$deliOrder['order_id']),'ship_status,order_bn,shop_id,is_delivery,mark_text,sync,order_id,self_delivery,createway,logi_id');

            //ExBOY加入部分发货时也回写
            if ($order['ship_status'] != '1' && $order['ship_status'] != '2') {
                return false;
            }

            $delivery['order'] = $order;
 
            //判断是否家装类
            $this->delivery_request($delivery);
        }

        $rs['rsp'] = 'success';
        return $rs;
    }

    protected function delivery_request($delivery)
    {
        $delivery = $this->format_delivery($delivery);
        if ($delivery === false) return false;

        $param = $this->getDeliveryParam($delivery);
        $callback = array(
           'class' => get_class($this),
           'method' => 'add_delivery_callback',
        );
        $shop_id = $delivery['shop_id'];
        $title = '店铺('.$this->_shop['name'].')添加[交易发货单](订单号:'.$param['tid'].',发货单号:'.$delivery['delivery_bn'].')';
        $addon['bn'] = $delivery['order']['order_bn'];
        
        #记录发货日志
        $oApi_log = app::get(self::_APP_NAME)->model('api_log');
        $log_id = $oApi_log->gen_id();
        
        $opInfo = kernel::single('ome_func')->getDesktopUser();
        #增加更新发货状态日志
        $log = array(
                'shopId'           => $shop_id,
                'ownerId'          => $opInfo['op_id'],
                'orderBn'          => $delivery['order']['order_bn'],
                'deliveryCode'     => $delivery['logi_no'],
                'deliveryCropCode' => $delivery['dly_corp']['type'],
                'deliveryCropName' => $delivery['logi_name'],
                'receiveTime'      => time(),
                'status'           => 'send',
                'updateTime'       => '0',
                'message'          => '',
                'log_id'           => $log_id,
        );
        $shipmentLogModel = app::get(self::_APP_NAME)->model('shipment_log');
        $shipmentLogModel->save($log);
        
        $orderModel = app::get(self::_APP_NAME)->model('orders');
        $orderModel->update(array('sync'=>'run'),array('order_id'=>$delivery['order']['order_id']));
        
        $write_log = array('log_id' => $log_id);
        $log_id = $this->_caller->request(LOGISTICS_OFFLINE_RPC,$param,$callback,$title,$shop_id,10,false,$addon,$write_log);

        return true;
    }

    protected function getDeliveryParam($delivery)
    {

        $item_list = array();
        foreach ($delivery['delivery_items'] as $k=>$v) {
            $item_list[$k]['oid'] = $delivery['order']['order_bn'];
            $item_list[$k]['itemId'] = $v['bn'];//取order_items上的商品ID
            $item_list[$k]['num'] = $v['number'];
        }

        $param = array(
            'tid'               => $delivery['order']['order_bn'],
            'company_code'      => $delivery['dly_corp']['type'],
            'company_name' => $delivery['logi_name'] ? $delivery['logi_name'] : '',
            'logistics_no'      => $delivery['logi_no'] ? $delivery['logi_no'] : '',
            'item_list'         => json_encode($item_list),
        );

        return $param;
    }

    /**
     * undocumented function
     *
     * @return void
     * @author 
     **/
    public function add_delivery_callback($result){
        if (!is_object($result)) return;
        $status = $result->get_status();
        
        $callback_params = $result->get_callback_params();
        $request_params     = $result->get_request_params();
        $log_id = $callback_params['log_id'];
        $shop_id = $callback_params['shop_id'];
        $order_bn = $request_params['tid'];
        
        
        $msg = json_decode($result->get_result(), true);
        if($msg){
            $msg = serialize($msg);
        }else{
            $msg = $result->get_result();
        }
        #返回结果中文提示
        $err_msg = $result->get_err_msg();
        if ($err_msg) {
            $msg .= '：'.$err_msg;
        }
        
        #增加订单状态回写
        $log = array('status' => $status, 'updateTime' => time(), 'message' => $msg);
        $logFilter = array('log_id' => $log_id);
        $shipment_log = app::get(self::_APP_NAME)->model('shipment_log');
        $shipment_log->update($log,$logFilter);
        
        
        if ($status == 'succ' || $status == 'fail'){
            if ( $order_bn && $shop_id ) {
                $orderModel = app::get(self::_APP_NAME)->model('orders');
                $data['up_time'] = time();
                $data['sync'] = $status;
                $_filter['order_bn'] = $order_bn;
                $_filter['shop_id'] = $shop_id;
                $orderModel->update( $data, $_filter);
            }
        }
        return parent::add_delivery_callback($result);
    }

    public function update_delivery_status($delivery , $status = '' , $queue = false)
    {}

    public function update_logistics($delivery,$queue = false)
    {}
    
    public function refund_apply_api($status){
        $api_method = '';
        switch($status){
            case '3':
                $api_method = REFUSE_REFUND;#拒绝退款接口
                break;
        }
        return $api_method;
    }   
    #更新退款单状态(此处主要是拒绝退款)
    public function  update_refund_apply_status($refund,$status,$mod = 'sync'){
        $orderModel = app::get(self::_APP_NAME)->model('orders');
        $order = $orderModel->dump($refund['order_id'], 'order_bn');
    
        $api_method = $this->refund_apply_api($status);
        if ($api_method == '') {
            return false;
        }
        $params['refund_id']  = $refund['refund_apply_bn'];
        $params['refuse_message']=$refund['refuse_message'];
        $params['tid'] = $order['order_bn'];
        $title = '店铺('.$this->_shop['name'].')更新[交易退款状态],(订单号:'.$order['order_bn'].'退款单号:'.$refundinfo['refund_apply_bn'].')';
        $addon['bn'] = $order['order_bn'];
        $shop_id = $this->_shop['shop_id'];
        $oApi_log = app::get(self::_APP_NAME)->model('api_log');
        $log_id = $oApi_log->gen_id();
        if ($mod == 'sync') {
            $timeout = 20;
            $rsp = $this->_caller->call($api_method, $params, $shop_id, $timeout);
    
            $callback = array(
                    'class'   => get_class($this),
                    'method'  => $api_method,
                    '2'       => array(
                            'log_id'  => $log_id,
                            'shop_id' => $shop_id,
                    ),
            );
            $api_status = 'running';
            if ($rsp->rsp == 'succ') {
                $api_status = 'success';
                $msg = '退款申请单状态更新成功<br>';
            }else{
                $api_status = 'fail';
                $err_msg = $rsp->err_msg ? $rsp->err_msg : $rsp->res;
                $msg = '退款申请单状态更新失败'.$err_msg.'<br>';
            }
            $params['msg_id'] =$rsp->msg_id;
            $oApi_log->write_log($log_id,$title,'apibusiness_router_request',$api_method,array($api_method, $params, $callback),'','request',$api_status,$msg,'','api.store.trade',$addon['bn']);
            $result['rsp']     = $rsp->rsp;
            $result['err_msg'] = $rsp->err_msg;
            $result['msg_id']  = $rsp->msg_id;
            $result['res']     = $rsp->res;
            $result['data']    = json_decode($rsp->data,1);
        }
        if(isset($result['msg']) && $result['msg']){
            $rs['msg'] = $result['msg'];
        }elseif(isset($result['err_msg']) && $result['err_msg']){
            $rs['msg'] = $result['err_msg'];
        }elseif(isset($result['res']) && $result['res']){
            $rs['msg'] = $result['res'];
        }
        $rs['rsp'] = $result['rsp'];
        return $rs;
    }    
}