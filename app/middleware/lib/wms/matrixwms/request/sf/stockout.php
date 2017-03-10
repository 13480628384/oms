<?php
/**
* 出库单
*
* @copyright shopex.cn 2014.05.07
* @author sunjing@shopex.cn
*/
class middleware_wms_matrixwms_request_sf_stockout extends middleware_wms_matrixwms_request_stockout{
     
    /**
     * 出库通知单参数
     * @param   array sdf
     * @return array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockout_create_params( $sdf , $cur_page )
    {
        $stockout_bn = $sdf['io_bn'];
        $items_count = count($sdf['items']);
        $total_page = ceil($items_count/$this->limit);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;
        $sdf_items = self::getItems($sdf['items'],$cur_page,$this->limit);

        if ($sdf_items){
            $items = array();
            $offset = ($cur_page-1) * $this->limit+1;
            
            foreach ($sdf_items as $v){
                
                $items[] = array(
                    'item_code' => $v['bn'],
                    'item_name' => $v['name'],
                    'item_quantity' => $v['num'],
                    'item_price' => $v['price'] ? $v['price'] : '0',// TODO: 商品价格
                    'item_line_num' => $offset,// TODO: 订单商品列表中商品的行项目编号，即第n行或第n个商品
                    'trade_code' => '',//可选(若是淘宝交易订单，并且不是赠品，必须要传订单来源编号) 
                    'item_id' => $v['bn'],// 商品ID
                    'is_gift' => '0',// TODO: 判断是否为赠品0:不是1:是
                    'item_remark' => '',// TODO: 商品备注
                    'inventory_type' => '1',// TODO: 库存类型1可销售库存101类型用来定义残次品201冻结类型库存301在途库存
                    'uom'=>'个',
                );
                $offset++;
            }
        }
        $wms_order_code = $stockout_bn;
        $order_type = $this->stockout_type[$sdf['io_type']] ? $this->stockout_type[$sdf['io_type']] : 'OUT_OTHER';
        $create_time = preg_match('/-|\//',$sdf['create_time']) ? $sdf['create_time'] : date("Y-m-d H:i:s",$sdf['create_time']);
        $monthaccount = app::get('wmsmgr')->getConf('monthaccount_'.$sdf['wms_id']);
        $params = array(
            'uniqid' => $sdf['batch_id'],
            'customer_id' => '',// 客户编码
            'out_order_code' => $stockout_bn,
            'order_type' => $order_type,
            'created' => $create_time,
            'wms_order_code' => $wms_order_code,
            'is_finished' => $cur_page >= $total_page ? 'true' : 'false',
            'current_page' => $cur_page,// 当前批次,用于分批同步
            'total_page' => $total_page,// 总批次,用于分批同步
            'shipping_type' => 'EXPRESS',// TODO: 运输方式 EXPRESS-快递 EMS-邮政速递
            'logistics_code' => '京东配送',// TODO: 快递公司（如果是汇购传递快递公司，则该项目不能为空，否则可以为空处理）
            'remark' => $sdf['memo'] ? $sdf['memo'] : '',
            'total_amount' => $sdf['total_goods_fee'],// 订单商品总价（精确到小数点后2位）
            'receiver_name' => $sdf['receiver_name']  ? $sdf['receiver_name'] : '顺丰',
            'receiver_zip' => $sdf['receiver_zip'] ? $sdf['receiver_zip'] : '200000',// TODO: 收货人邮政编码
            'receiver_state' => $sdf['receiver_state'] ? $this->_formate_receiver_citye($sdf['receiver_state']) : '上海市',// TODO: 退货人所在省
            'receiver_city' => $sdf['receiver_city'] ? $sdf['receiver_city'] : '上海',// TODO: 退货人所在市
            'receiver_district' => $sdf['receiver_district'] ? $sdf['receiver_district'] : '',// TODO: 退货人所在县（区），注意有些市下面是没有区的
            'receiver_address' => $sdf['receiver_address'] ? $sdf['receiver_address'] : '',// TODO: 收货地址（出库时非空）
            'receiver_phone' => $sdf['receiver_phone'] ? $sdf['receiver_phone'] : '021-22222222',// TODO: 收货人电话号码（如有分机号用“-”分隔）(电话和手机必选一项) 
            'receiver_mobile' => $sdf['receiver_mobile'] ? $sdf['receiver_mobile'] : '13222222222',// TODO: 收货人手机号码(电话和手机必选一项) 
            'receiver_email ' => $sdf['receiver_email'] ? $sdf['receiver_email'] : '未知',// TODO: 收货人手机号码(电话和手机必选一项) 
            'receiver_country'=>'中国',
            'receiver_time' => '',
            'sign_standard' => '',// TODO: 签收标准（如：身仹证150428197502205130）
            'source_plan' => '',// TODO: 来源计划点
            'line_total_count' => $sdf['line_total_count'],// TODO: 订单行项目数量
            'item_total_num' => $sdf['item_total_num'],
            'storage_code' => $sdf['storage_code'],// 库内存放点编号
            'items' => json_encode(array('item'=>$items)),
            'is_cod'=>'false',
            //'logi_code'=>'京东配送',
            'platform_order_code'=>'',//平台单号
            'payment_of_charge'=>'',
            'monthly_account'=>$monthaccount,
        );
        return $params;
    }

    /**
     * 出库单取消通知参数
     * @param   array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockout_cancel_params( $sdf )
    {
        
        $stockout_bn = $sdf['io_bn'];
        $order_type = $this->stockout_type[$sdf['io_type']] ? $this->stockout_type[$sdf['io_type']] : 'OUT_OTHER';

        $params = array(
            'out_order_code' => $stockout_bn,
            'order_type' => $order_type,
             'uniqid' =>self::uniqid(),
        );
        return $params;
    }

    
    
    
}