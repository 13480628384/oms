<?php
class apibusiness_response_order_vop_b2cv1 extends  apibusiness_response_order_vop_abstract{ 
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
            
    public function canCreate(){
        if ($this->_ordersdf['status'] != 'active') {
            $this->_apiLog['info']['msg'] = ($this->_ordersdf['status'] == 'dead') ? '取消的订单不接收' : '完成的订单不接收';
            return false;
        }
        #唯品会创建订单的时候，未支付订单不接受
        if($this->_ordersdf['pay_status'] != '1'){
            $this->_apiLog['info']['msg'] =  '未支付唯品会订单不接收';
            return false;
        }
    }

}