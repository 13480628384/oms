<?php
#mia(蜜芽宝贝)订单处理 抽象类
abstract class apibusiness_response_order_mia_abstract extends apibusiness_response_order_abstractbase
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

        #未支付的款到发货订单拒收
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
        $components = array('markmemo','custommemo');
        $process_status = array('unconfirmed');
        #未审核的订单，修改收货人信息
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
            #如果ERP收货人信息未发生变动时，则更新收货人信息
            if($local_updated == false){
                #还要判断是未审核,审核的才修改
                $components[] = 'consignee';
            }
      }
    
        return $components;
    }     
}