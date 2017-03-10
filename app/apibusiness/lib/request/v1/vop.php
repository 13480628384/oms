<?php
/**
* vop(唯品会)接口请求实现
*/
class apibusiness_request_v1_vop extends apibusiness_request_partyabstract{
    #获取发货参数
    protected function getDeliveryParam($delivery){
        
        $product_list = array();
        $pkg_pd_logi_no = array();
        
        $orderObjModel = app::get(self::_APP_NAME)->model('order_objects');
        $orderObj = $orderObjModel->getList('shop_goods_id,quantity',array('order_id'=>$delivery['order']['order_id']));
        foreach ($orderObj as $obj) {
            if($obj['shop_goods_id']){
                $delivery_items= array(
                        'barcode' => $obj['shop_goods_id'],
                        'amount' => $obj['quantity'],
                );
                $product_list[] = $delivery_items;
            }
        }
        
        $pkg_pd_logi_no[] = array(
                'package_product_list' => $product_list,
                'transport_no' => $delivery['logi_no'] ? $delivery['logi_no'] : ''
        );
        
        $param = array(
                'tid'               => $delivery['order']['order_bn'],
                'company_code'      => $delivery['dly_corp']['type'],
                'package_type'      => 1, //唯品会拆单发货也只能回写全部一次性完成 这里写死为1
                'packages'	=> json_encode($pkg_pd_logi_no),
        );
        
        return $param;
        
    } 
}
