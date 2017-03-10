<?php
/**
* 发货单
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_ilcwms_request_delivery extends middleware_wms_ilcwms_request_common{

    /**
    * 发货单创建
    * @access public
    * @param Array $sdf 发货单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function delivery_create(&$sdf,$sync=false){

        $delivery_bn = $sdf['outer_delivery_bn'];

        // 状态判断,发货单状态为取消，则不发起同步
        if($this->iscancel($delivery_bn,'delivery')){
            return $this->msgOutput('success','发货单已取消,终止同步');
        }


        $items = array();
        foreach ($sdf['delivery_items'] as $v){
            $barcode = $this->getBarcode($v['bn']);#TODO:伊腾忠用条形码作唯一标识
            $items[] = array(
                'item_bn' => $barcode,
                'price' => $v['price'],
                'item_sale_price' => $v['sale_price'],
                'num' => $v['number'],
            );
        }

        $params = array(
            'order_type' => $sdf['shop_type'],
            'order_bn' => $delivery_bn,
            'outer_order_bn' => $sdf['outer_order_bn'],
            'original_order_bn' => $sdf['order_bn'],
            'warehouse' => $sdf['branch_bn'],
            'logistics' => $sdf['wms_logi_code']     ? $sdf['wms_logi_code'] : $sdf['logi_code'],
            'member_name' => $sdf['member_name'],
            'ship_name' => $sdf['consignee']['name'],
            'zip' => $sdf['consignee']['zip'],
            'phone' => $sdf['consignee']['telephone'],
            'mobile' => $sdf['consignee']['mobile'],
            'province' => $sdf['consignee']['province'],
            'city' => $sdf['consignee']['city'],
            'district' => $sdf['consignee']['district'] ? $sdf['consignee']['district'] : '其他',
            'addr' => $sdf['consignee']['addr'],
            'cost_item' => $sdf['total_amount'],
            'cost_freight' => $sdf['logistics_costs'],
            'is_cod' => $sdf['is_cod'],
            'cod_fee' => $sdf['cod_fee'],
            'shop_code' => $sdf['shop_code'],#售达方编号
            'delivery_center' => $sdf['print_remark']['pszx_name'],#配送中心名称
            'memo' => $sdf['memo'],
            'items' => json_encode($items),
        );

        $writelog = array(
            'log_title' => '发货单添加',
            'log_type' => 'store.trade.delivery',
            'action_type' => 'DELIVERY.WMS',
            'original_bn' => $delivery_bn,
        );
        $method = 'store.wms.saleorder.create';

        return $this->request($method,$params,$writelog,$sync);
        
    }

    /**
    * 发货单取消
    * @access public
    * @param Array $sdf 发货单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function delivery_cancel(&$sdf,$sync=false){
        return array('rsp'=>'fail','msg'=>'接口方法不存在','msg_code'=>'w402');
    }

}