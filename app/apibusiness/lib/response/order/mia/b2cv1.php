<?php
class apibusiness_response_order_mia_b2cv1 extends  apibusiness_response_order_mia_abstract{ 
    /**
     * 是否接收(除活动订单外的其他订单)
     *
     * @return void
     * @author
     **/
    protected function accept_dead_order(){
        $rs = parent::accept_dead_order();
        #订单取消的，先放过
        if ($rs == false && $this->_ordersdf['status'] == 'dead') {
            unset($this->_apiLog['info']['msg']);
            return true;
        }
        return $rs;
    }
    /*     
     * 是否接收订单
    * @return void
    * @author
    **/
    protected function canAccept(){
        $result = parent::canAccept();
        if ($result === false) {
            return false;
        }
        #未支付的订单拒收
        if ($this->_ordersdf['pay_status'] == '0') {
            $this->_apiLog['info']['msg'] = '未支付订单不接收';
            return false;
        }
        return true;
    }
            
    /**
     * @return void
     * @author
     **/
    public function canCreate(){
        if ($this->_ordersdf['status'] != 'active') {
            $this->_apiLog['info']['msg'] = ($this->_ordersdf['status'] == 'dead') ? '取消的订单不接收' : '完成的订单不接收';
            return false;
        }
        #蜜芽创建订单的时候，未支付订单不接受
        if($this->_ordersdf['pay_status'] != '1'){
            $this->_apiLog['info']['msg'] =  '未支付蜜芽宝贝订单不接收';
            return false;
        }
    }
    
    #更新订单前的操作
    public  function preUpdate(){
        if($this->_ordersdf['status'] == 'dead'){
            #1、未发货的订单，整单更新为退款中
            if (($this->_tgOrder['status'] == 'active') &&($this->_tgOrder['ship_status'] == '0')) {
                #本地支付状态是1的才更新为退款中
                if($this->_tgOrder['pay_status'] == '1'){
                    $this->_newOrder['pay_status'] = '7';
                    $this->_newOrder['pause'] = 'true';
                    $logModel = app::get('ome')->model('operation_log');
                    $logModel->write_log('order_edit@ome',$this->_tgOrder['order_id'],'更新订单状态为退款中');
                }
            }
            #2、本地已退款或已完成
            else {
                $this->_apiLog['info'][] = '返回值:已完成订单不更新!';
                return false;
            }
        }
    }
}