<?php
/**
* bbc(bbc系统)抽象类
*
* @category apibusiness
* @package apibusiness/response/order/shopex/485
* @author chenping<chenping@shopex.cn>
* @version $Id: abstract.php 2013-3-12 17:23Z
*/
abstract class apibusiness_response_order_shopex_bbc_abstract extends apibusiness_response_order_shopex_abstract
{
    /**
     * 数据转
     *
     * @return void
     * @author 
     **/
    public function reTransSdf()
    {
        parent::reTransSdf();

        foreach ($this->_ordersdf['order_objects'] as $key_obj => $value_obj) {
            foreach ($value_obj['order_items'] as $key_item => $value_item) {
                $this->_ordersdf['order_objects'][$key_obj]['order_items'][$key_item]['item_type'] = ($value_item['item_type'] == 'goods') ? 'product' : $value_item['item_type'];
            }
        }
    }   
    /**
     * 是否接收(除活动订单外的其他订单)
     *
     * @return void
     * @author
     **/
    protected function accept_dead_order(){
        $rs = parent::accept_dead_order();
        #取消或已经完成的，全放过，在canCreate合canUpdate中继续判断
        if ($rs == false) {
            unset($this->_apiLog['info']['msg']);
            return true;
        }
        #本地还未发货的，需要取消
        return $rs;
    }  
    #是否能创建
   public  function canCreate(){
       $result = parent::canAccept();
       if ($result === false) {
           return false;
       }
       if ($this->_ordersdf['status'] != 'active') {
           if ($this->_ordersdf['status'] == 'close') {
               $this->_apiLog['info']['msg'] = '取消的订单不接收';
           } else {
               $this->_apiLog['info']['msg'] = '完成的订单不接收';
           }
           return false;
       }
       $bbc_pay_status = array('6','7');#退款中的订单，不接受
       if(in_array($this->_ordersdf['pay_status'], $bbc_pay_status)) {
           $this->_apiLog['info']['msg'] = '退款中的订单，不接受!';
           return false;
       }
   }
   #是否能更新
   public function canUpdate(){
       $bbc_pay_status = array('6','7');#退款中的订单，不接受
       if(in_array($this->_ordersdf['pay_status'], $bbc_pay_status)) {
           $this->_apiLog['info']['msg'] = '退款中的订单，不接受!';
           return false;
       }
       $orderModel = app::get(self::_APP_NAME)->model('orders');
       $payStatus = array('0','1','2','3','4','5','6','7','8');
       #OMS中，订单已经取消
       if ($this->_tgOrder['process_status'] == 'cancel' || $this->_tgOrder['status'] == 'dead') {
           if ($this->_ordersdf['pay_status'] != $this->_tgOrder['pay_status'] && in_array($this->_ordersdf['pay_status'], $payStatus)) {
               $order['pay_status'] = $this->_ordersdf['pay_status'];
       
               $this->_apiLog['info'][] = '订单结构变化：更新订单支付状态为：'.$order['pay_status'];
           }
       
           if ($this->_ordersdf['payed'] != $this->_tgOrder['payed']) {
               $order['payed'] = $this->_ordersdf['payed'];
       
               $this->_apiLog['info'][] = '订单结构变化：更新订单付款金额为：'.$order['payed'];
           }
       
           if ($order) {
               $orderModel->update($order,array('order_id'=>$this->_tgOrder['order_id']));
       
               $logModel = app::get(self::_APP_NAME)->model('operation_log');
               $logModel->write_log('order_edit@ome',$this->_tgOrder['order_id'],"前端店铺订单更新");
           }
       
           return false;
       } else {
           #款到发货，前端已经退款取消
           if($this->_ordersdf['status'] == 'dead' && $this->_ordersdf['pay_status'] == '5' ){
               #未付款取消和已付款取消
               $tg_pay_status = array('6','7');
               $rs['rsp'] = 'fail';
               #已支付的取消（添加退款单到bbc时，本地支付状态属于退款中、退款申请中）
               if($this->_tgOrder['ship_status'] =='0' && $this->_tgOrder['status'] == 'active' && in_array($this->_tgOrder['pay_status'], $tg_pay_status)){
                   $up_data = array();
                   $up_data['pay_status'] = $this->_ordersdf['pay_status'];#更新支付状态未全额退款
                   $up_data['payed'] = 0;
                   $memo = '前端店铺:'.$this->_shop['name'].'订单作废';
                   $orderModel->update($up_data,array('order_id'=>$this->_tgOrder['order_id']));
                   $rs = $orderModel->cancel($this->_tgOrder['order_id'],$memo,false,'async');
               }
               #未支付的取消
               elseif($this->_tgOrder['ship_status'] =='0' && $this->_tgOrder['status'] == 'active' && $this->_tgOrder['pay_status']=='0'){
                   $memo = '前端店铺:'.$this->_shop['name'].'订单作废';
                   $rs = $orderModel->cancel($this->_tgOrder['order_id'],$memo,false,'async');
               }
               if($rs['rsp'] == 'fail') {
                   $this->_apiLog['info'][] = '返回值：订单已发货无法取消或者取消失败';
                   $this->exception('add');
               } else {
                   $this->_apiLog['info'][] = '返回值：订单取消成功！';
               }
               return false;
           }
          #货到付款,前端已经取消
          elseif($this->_ordersdf['shipping']['is_cod'] == 'true' && $this->_ordersdf['status'] == 'dead' && $this->_ordersdf['pay_status'] == '0'){
              $up_data['pay_status'] = $this->_ordersdf['pay_status'];
               $up_data['payed'] = 0;
               $orderModel->update($up_data,array('order_id'=>$this->_tgOrder['order_id']));
               
               $memo = '前端店铺:'.$this->_shop['name'].'货到付款订单取消';
               $rs = $orderModel->cancel($this->_tgOrder['order_id'],$memo,false,'async');
               if($rs['rsp'] == 'fail') {
                  $this->_apiLog['info'][] = '返回值：货到付款订单取消失败';
                  $this->exception('add');
               } else {
                  $this->_apiLog['info'][] = '返回值：订单取消成功！';
              }
              return false;
          }
       }
       #bbc货到付款，线下线下都已发货，需要再更新相关支付状态和支付单
       if (($this->_ordersdf['shipping']['is_cod'] == 'true' && $this->_ordersdf['status'] == 'finish' && $this->_ordersdf['ship_status'] =='1') && $this->_tgOrder['ship_status'] == '1') {
           
           //$this->_apiLog['info'][] = '返回值：已发货的货到付款订单，不接受！';
           //$this->exception('update');
           //return false;
       }
       return true;
   }   
}