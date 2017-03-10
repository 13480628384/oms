<?php
class apibusiness_response_order_wdwd_b2cv1 extends  apibusiness_response_order_wdwd_abstract{
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
        $result =  parent::canCreate();
        if($result === false){
            return false;
        }
        if ($this->_ordersdf['status'] != 'active') {
            $this->_apiLog['info']['msg'] = ($this->_ordersdf['status'] == 'dead') ? '取消的订单不接收' : '完成的订单不接收';
            return false;
        }
        #有量创建订单的时候，未支付订单不接受
        if($this->_ordersdf['pay_status'] != '1'){
            $this->_apiLog['info']['msg'] =  '未支付有量订单不接收';
            return false;
        }
        return true;
    }

    #更新订单前的操作
    public  function preUpdate(){
        $rs = parent::canUpdate();

        // 全额退款，订单取消
        if ($this->_ordersdf['status'] == 'dead') {

            if ($this->_tgOrder['status'] == 'active' && $this->_tgOrder['ship_status'] == '0') {
                $orderModel = app::get(self::_APP_NAME)->model('orders');

                if ($this->_tgOrder['pay_status'] == '6') {
                    $ordersdf = array(
                        'pay_status' => '5',
                        'payed' => '0',
                    );
                    $orderModel->update($ordersdf,array('order_id' => $this->_tgOrder['order_id']));
                }

                $memo = '前端订单取消';
                $orderModel->cancel($this->_tgOrder['order_id'],$memo,false,'async');

                $this->_apiLog['info'][] = '返回值：订单取消成功';

                $this->shutdown('add');

                return false;
            } else {
                $this->_apiLog['info'][] = '返回值：取消订单不更新';

                return false;
            }
        } elseif($this->_ordersdf['status'] == 'finish') {
            $orderModel = app::get(self::_APP_NAME)->model('orders');

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
}