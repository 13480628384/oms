<?php
/**
* dangdang(当当平台)订单处理 抽象类
*
* @category apibusiness
* @package apibusiness/response/order/dangdang
* @author chenping<chenping@shopex.cn>
* @version $Id: abstract.php 2013-3-12 17:23Z
*/
abstract class apibusiness_response_order_dangdang_abstract extends apibusiness_response_order_abstractbase
{
    protected function analysis()
    {
        parent::analysis();

        // 用支付单判断订单状态
        $payment_list = isset($this->_ordersdf['payments']) ? $this->_ordersdf['payments'] : array($this->_ordersdf['payment_detail']);
        if ($payment_list[0] && $this->_ordersdf['payed']=='0') {

            foreach ($payment_list as $key => $value) {
                $this->_ordersdf['payed'] += $value['money'];
            }

            if ($this->_ordersdf['total_amount'] <= $this->_ordersdf['payed']) {
                $this->_ordersdf['pay_status'] = '1';
            } elseif ($this->_ordersdf['payed'] <= 0) {
                $this->_ordersdf['pay_status'] = '0';
            } else {
                $comp = bccomp($this->_ordersdf['payed'], $this->_ordersdf['total_amount'],3);
                if ($comp<0) {
                    $this->_ordersdf['pay_status'] = '3';
                } else {
                    $this->_ordersdf['pay_status'] = '1';
                }
            }
        }

        if (!$this->_ordersdf['createtime']) $this->_ordersdf['createtime'] = time();
    }

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
     * 订单转换淘管格式
     *
     * @return void
     * @author 
     **/
    public function component_convert()
    {

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
        $components = array('markmemo','custommemo','marktype');

        return $components;
    }
}