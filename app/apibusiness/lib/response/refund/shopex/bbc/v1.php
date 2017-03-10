<?php
#wangkezheng
class apibusiness_response_refund_shopex_bbc_v1 extends apibusiness_response_refund_v2
{

    protected function canAccept($tgOrder=array())
    {
        
        if ($tgOrder['ship_status'] == '1') {
            
            $this->_apiLog['info']['msg'] = '订单['.$this->_refundsdf['order_bn'].']已经发货，无法退款';
            return false;
        }
        return parent::canAccept($tgOrder);

    }
}