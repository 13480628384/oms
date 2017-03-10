<?php
/**
* 退货单
*
* @copyright shopex.cn 2014.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_sf_reship extends middleware_wms_matrixwms_request_reship{

   
    /**
     * 退货单添加退货退知参数
     *@access protected
    * @param array
    */

    protected function _getReship_create_params($sdf)
    {
        $reship_bn = $sdf['reship_bn'];
        $items_count = count($sdf['items']);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;

        if ($sdf['items']){
            $offset = 0;
            $items = array();
            foreach ($sdf['items'] as $v){
                // 获取外部商品sku
                $items[] = array(
                    'item_code' => $v['bn'],
                    'item_name' => $v['name'],
                    'item_quantity' => $v['num'],
                    'item_price' => $v['price'] ? $v['price'] : '0',// TODO: 商品价格
                    'item_line_num' => $offset,// TODO: 订单商品列表中商品的行项目编号，即第n行或第n个商品
                    'trade_code' => '',//可选(若是淘宝交易订单，并且不是赠品，必须要传订单来源编号) 
                    'item_id' => $outer_sku,// 商品ID
                    'is_gift' => '0',// TODO: 判断是否为赠品0:不是1:是
                    'item_remark' => '',// TODO: 商品备注
                    'inventory_type' => '1',// TODO: 库存类型1可销售库存101类型用来定义残次品201冻结类型库存301在途库存
                    'uom'=>'个',
                );
                $offset++;
            }
        }

        $params = array(
            'uniqid' => self::uniqid(),
            'wms_supplier' => '',//    TODO: 服务提供商编号
            'out_order_code' => 'MS'.$reship_bn,
            'warehouse_code' => $sdf['branch_bn'],
            'orig_order_code' => $this->_getout_delivery_bn($sdf['original_delivery_bn']),//'OS'.$sdf['original_delivery_bn']
            'created' => $sdf['create_time'],
            'logistics_no' => $sdf['logi_no'],
            'logistics_code' => $sdf['logi_name'],//物流公司
            'remark' => $sdf['memo'],
            'platform_order_code'=>$sdf['order_bn'],//订单号
            'wms_order_code' => $reship_bn,
            'is_finished' => 'true',
            'current_page' => '1',// 当前批次,用于分批同步
            'total_page' => '1',// 总批次,用于分批同步
            'receiver_name' => $sdf['receiver_name'],
            'receiver_zip' => $sdf['receiver_zip'],
            'receiver_state' => $sdf['receiver_province'],
            'receiver_city' => $sdf['receiver_city'],
            'receiver_district' => $sdf['receiver_district'],
            'receiver_address' => $sdf['receiver_addr'],
            'receiver_phone' => $sdf['receiver_tel'],
            'receiver_mobile' => $sdf['receiver_mobile'],
            'receiver_email' => $sdf['receiver_email'],
            'sign_code' => '',// TODO: 节点标识，请求唯一标识 
            'dest_plan' => '',// TODO: 目的计划点
            'line_total_count' => $sdf['line_total_count'],// TODO: 订单行项目数量
            'storage_code' => $sdf['storage_code'],// 库内存放点编号
            'items' => json_encode(array('item'=>$items)),
            'monthly_account'=>'7550144315',
            'expect_end_time'=>date('Y-m-d H:i:s',strtotime('+1 days')),//预计收货时间
            'source_id'=>$sdf['supplier_bn'] ? $sdf['supplier_bn']: 'SHP_V1',
        );
        return $params;
    }
}