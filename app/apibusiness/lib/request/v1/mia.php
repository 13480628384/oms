<?php
/**
* mia(蜜芽宝贝)接口请求实现
* 密芽宝贝，发货回写，实际是要打密芽的两个接口：1、订单打单确认(mia.order.confirm);2、订单出库升级版(mia.order.deliver.upgrade)
*/
class apibusiness_request_v1_mia extends apibusiness_request_partyabstract{
    #获取发货参数
    protected function getDeliveryParam($delivery){
        $item_list = array();
        #往前端回写的是order_object,而不是items
        $order_objects = app::get(self::_APP_NAME)->model('order_objects');
        $items = $order_objects->getList('shop_goods_id',array('order_id'=>$delivery['order']['order_id']));
        
        $item_id = array();
        foreach ($items as $v) {
           $item_id[] = $v['shop_goods_id'];
        }
        $str_item_id = '';
        if(!empty($item_id)){
           $str_item_id = implode(',',$item_id);
        }
        $param = array(
                'tid'               => $delivery['order']['order_bn'],
                'company_code'      => $delivery['dly_corp']['type'],
                'company_name' => $delivery['logi_name'] ? $delivery['logi_name'] : '',
                'logistics_no'      => $delivery['logi_no'] ? $delivery['logi_no'] : '',
                'item_id'         => $str_item_id
        );
        return $param;
    } 
}
