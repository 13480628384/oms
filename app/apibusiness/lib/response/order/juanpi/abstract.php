<?php
/**
* juanpi(卷皮)订单处理 抽象类
*/
abstract class apibusiness_response_order_juanpi_abstract extends apibusiness_response_order_abstractbase{
    #是否接收订单
    protected function canAccept(){
        $result = parent::canAccept();
        if ($result === false) {
            return false;
        }
        #未支付订单拒收
        if ($this->_ordersdf['pay_status'] == '0') {
            $this->_apiLog['info']['msg'] = '未支付订单不接收';
            return false;
        }
        return true;
    }
    #订单转换淘管格式
    public function component_convert(){;
        parent::component_convert();
        $this->_newOrder['pmt_goods'] = abs($this->_newOrder['pmt_goods']);
        $this->_newOrder['pmt_order'] = abs($this->_newOrder['pmt_order']);
    }
   #需要去更新的组件
    protected function get_update_components(){
        $components = array('markmemo','custommemo');
        $process_status = array('unconfirmed');
        #未审核的卷皮订单，修改收货人信息
        if(in_array($this->_tgOrder['process_status'], $process_status)){
            $obj_orders_extend = app::get('ome')->model('order_extend');
            $rs = $obj_orders_extend->getList('extend_status',array('order_id'=>$this->_tgOrder['order_id']));
            #判断本地收货人信息，是否发生变更
            if($rs[0]['extend_status'] == 'consignee_modified'){
                #ERP已修改
                $local_updated = true;
            }else{
                #ERP未修改
                $local_updated = false;
            }
            #如果ERP收货人信息未发生变动时，则更新淘宝收货人信息
            if($local_updated == false){
                #还要判断是未审核才修改
                $components[] = 'consignee';
            }
        }
        return $components;
    }
    #卷皮原始数据上的状态（无售后，售后中，售后完成，售后关闭）
    #矩阵对应卷皮的状态array(0=>'active',1=>'refunding',2=>'close',3=>'refund_close');
/*     public  function preCreate(){
        $trade_refunding = false;
        foreach ($this->_ordersdf['order_objects'] as $objkey => &$object) {
            foreach ($object['order_items'] as $k => &$v) {
                #有售后的，统统不接
                if ($v['status'] != 'active') {
                    $trade_refunding = true;
                    break;
                }
            }
        }
        #售后中，不接受
        if ($trade_refunding == true) {
            $this->_apiLog['info'][] = "接受参数：".var_export($this->_ordersdf,true);
            $this->_apiLog['info']['msg'] = '卷皮有售后订单，不接收！';
            $this->exception(__METHOD__);
            return false;
        }
    } */
    #卷皮原始数据上的几个状态（无售后，售后中，售后完成，售后关闭）
    #更新订单前的操作（只接受无售后;凡是有售后的，都不要了）   
    public  function preUpdate(){
        $status = array(
                0=>array('active'),
                #有退款或退款已经完成
                1=>array('refunding','close','refund_close'),
        );
        $trade_refund = false;
        foreach ($this->_ordersdf['order_objects'] as $objkey => &$object) {
            foreach ($object['order_items'] as $k => &$v) {
                #有售后
                if($v['status'] !='active') {
                    $trade_refund = true;
                    continue;
                }
            }
        }
        #更新时，只要一个明细有有售后的，整单更新为退款中
        if (($trade_refund == true) && ($this->_tgOrder['status'] == 'active') &&($this->_tgOrder['pay_status'] == '1')) {
            $this->_newOrder['pay_status'] = '7';
            $this->_newOrder['pause'] = 'true';
        }
    }
    #能够创建订单
    public function canCreate(){
        if ($this->_ordersdf['ship_status'] != '0') {
            $this->_apiLog['info']['msg'] = '已发货订单不接收';
            return false;
        }
        #调整售后的状态
        $this->reTransSdf_refund();
        #纠正卷皮的优惠
        $this->reTransSdf_pmt_order();
        return true;
    }
    public function canUpdate(){
        #纠正卷皮的优惠
        $this->reTransSdf_pmt_order();
    }  
    #重新调整相关订单明细活跃状态
    public function  reTransSdf_refund(){
        $status = array(
                0=>array('active'),
                #有退款或退款已经完成
                1=>array('refunding','close','refund_close'),
        );
        foreach($this->_ordersdf['order_objects'] as $obj_key=>$order_items){
            if($order_items){
                foreach($order_items['order_items'] as $item_key=>$items){
                    if(in_array($items['status'],$status[1])){
                        $this->_ordersdf['order_objects'][$obj_key]['order_items'][$item_key]['status'] = 'close';
                    }
                }
            }
        }
        return true;
    } 
    #重新纠正卷皮的优惠(卷皮平台的优惠，需要ERP自己算。订单总优惠=商品金额-实收金额)
    public function  reTransSdf_pmt_order(){
        $pmt_order = $this->_ordersdf['cost_item']-$this->_ordersdf['payed'];
        if($pmt_order > 0){
            $this->_ordersdf['pmt_order'] = $pmt_order;
        }
        return true;
    }    
    protected function postUpdate(){
        $rs = parent::canUpdate();
        #前端已退款或已退货
        if ($this->_ordersdf['status'] == 'dead') {
            #1、本地未发货
            if ($this->_tgOrder['status'] == 'active' && $this->_tgOrder['ship_status'] == '0') {
                $orderModel = app::get(self::_APP_NAME)->model('orders');
                if ($this->_tgOrder['pay_status'] == '7') {
                    $ordersdf = array(
                            'pay_status' => '5',
                            'payed' => '0',
                    );
                    $orderModel->update($ordersdf,array('order_id' => $this->_tgOrder['order_id']));
                }
                $memo = '前端订单取消';
                #并且需要取消订单
                $orderModel->cancel($this->_tgOrder['order_id'],$memo,false,'async');   
                $this->_apiLog['info'][] = '返回值：订单取消成功';
                $this->shutdown('add');
                return false;
            } 
            #2、本地已退款或已完成
            else {
                $this->_apiLog['info'][] = '返回值:已完成订单不更新!';
                return false;
            }
        } 
       #前端已发货完成
       elseif($this->_ordersdf['status'] == 'finish') {
            $orderModel = app::get(self::_APP_NAME)->model('orders');
            #前端已发货，本地还是是退款中的，改为已支付(ERP继续发货)
            if ($this->_ordersdf['pay_status'] == '1' && $this->_tgOrder['pay_status'] == '7') {
                $ordersdf = array(
                        'pay_status' => '1',
                );
                $orderModel->update($ordersdf,array('order_id' => $this->_tgOrder['order_id']));    
                $this->_apiLog['info'][] = '前端拒绝退款并手动发货，后端更新支付状态：1';
                $this->shutdown('add');
            } else {
                $this->_apiLog['info']['msg'] = '完成的订单不接收';
                $this->exception('add');
            }
            return false;
       } 
       return $rs;
    } 
    
   #是否接收(除活动订单外的其他订单)
    protected function accept_dead_order(){
        $rs = parent::accept_dead_order();
        if ($rs == false && $this->_ordersdf['status'] == 'dead') {
            unset($this->_apiLog['info']['msg']);
            return true;
        }
        if ($rs == false && $this->_ordersdf['status'] == 'finish') {
            unset($this->_apiLog['info']['msg']);
            return true;
        }
        return $rs;
    }              
}