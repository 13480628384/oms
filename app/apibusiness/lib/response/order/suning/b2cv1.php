<?php
/**
* suning(苏宁平台)直销订单处理 版本一
*
* @category apibusiness
* @package apibusiness/response/order/suning
* @author chenping<chenping@shopex.cn>
* @version $Id: b2cv1.php 2013-3-12 17:23Z
*/
class apibusiness_response_order_suning_b2cv1 extends apibusiness_response_order_suning_abstract
{


    /**
     * 是否接收(除活动订单外的其他订单)
     *
     * @return void
     * @author 
     **/
    protected function accept_dead_order(){

        // 接收退款完成订单
        if ($this->_ordersdf['status'] == 'dead') {
            return true;
        }

        return parent::accept_dead_order();
        
    }


    /**
     * 允许更新
     *
     * @return void
     * @author 
     **/
    protected function canUpdate()
    {
        if ($this->_ordersdf['status'] == 'dead') {
            if ($this->_tgOrder['status'] == 'active' && $this->_tgOrder['ship_status'] == '0') {
                $orderModel = app::get(self::_APP_NAME)->model('orders');

                // 款到发货
                if ($this->_ordersdf['shipping']['is_cod'] == 'false') {
                    if ($this->_ordersdf['pay_status'] == '5' && $this->_tgOrder['pay_status']=='6') {
                        $ordersdf = array('pay_status' => '5','payed' => '0',);
                        $filter = array('order_id'=>$this->_tgOrder['order_id']);
                        $orderModel->update($ordersdf,$filter);

                        $orderModel->cancel($this->_tgOrder['order_id'],'前端订单取消',false,'async');

                        $this->_apiLog['info'][] = '返回值：订单取消成功';
                    }
                } elseif ($this->_ordersdf['shipping']['is_cod']=='true') {
                    $orderModel->cancel($this->_tgOrder['order_id'],'前端订单取消',false,'async');

                    $this->_apiLog['info'][] = '返回值：订单取消成功';
                }

                $this->shutdown('add');

                return false;
            } else {
                $this->_apiLog['info'][] = '返回值：取消订单不更新';

                return false;
            }
        }

        return parent::canUpdate();
    }
            
}