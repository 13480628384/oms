<?php
/**
* youzan(有赞)订单处理 抽象类
* wangkezheng
*/
abstract class apibusiness_response_order_youzan_abstract extends apibusiness_response_order_abstractbase
{
    /**
     * 是否接收订单
     *
     * @return void
     * @author 
     **/
     protected function canAccept()
    {
        $result = parent::canAccept();
        if ($result === false) {
            return false;
        }

        # 未支付的款到发货订单拒收
        if ($this->_ordersdf['shipping']['is_cod'] != 'true' && $this->_ordersdf['pay_status'] == '0') {
            $this->_apiLog['info']['msg'] = '未支付订单不接收';
            return false;
        }

        return true;
    } 

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
     * 能够创建订单
     *
     * @return void
     * @author 
     **/
    public function canCreate()
    {
        // 卡住死单
        if ($this->_ordersdf['status'] != 'active') {
            $this->_apiLog['info']['msg'] = ($this->_ordersdf['status'] == 'dead') ? '取消的订单不接收' : '完成的订单不接收';
            return false;
        }     

        return parent::canCreate();
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

                    // 未发货取消订单,
                    if ( $this->_ordersdf['pay_status'] == '5' && in_array($this->_tgOrder['pay_status'],array('6','1')) ) {
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

    /**
     * 订单转换淘管格式
     *
     * @return void
     * @author
     **/
    public function component_convert(){
        parent::component_convert();
    
        $this->_newOrder['pmt_goods'] = abs($this->_newOrder['pmt_goods']);
        $this->_newOrder['pmt_order'] = abs($this->_newOrder['pmt_order']);
    }

    /**
     * 需要更新的组件
     *
     * @return void
     * @author
     **/
    protected function get_update_components()
    {
        $components = array('markmemo','custommemo');

        if ($this->_tgOrder['pay_status'] == '1' && $this->_ordersdf['pay_status'] == '6') {
            $components[] = 'master';
        }

        return $components;
    }

   /**
     * 更新订单
     *
     * @return void
     * @author 
     **/
    public function updateOrder()
    {
        parent::updateOrder();
        
        if ($this->_newOrder) {
            // 叫回发货单
            kernel::single('apibusiness_notice')->notice_process_order($this->_tgOrder,$this->_newOrder);
        }
    }     
}