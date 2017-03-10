<?php
/**
* 入库单
*
* @copyright shopex.cn 2014.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_sf_stockin extends middleware_wms_matrixwms_request_stockin{
    
    /**
     * 入库创建通知单参数
     * @param   array sdf
     * @return  sdf
     * @access  protected
     * @author cyyr24@sina.cn
     */
    protected  function _getStockin_create_params($sdf,$cur_page)
    {

        $stockin_bn = $sdf['io_bn'];
        $items_count = count($sdf['items']);
        $total_page = ceil($items_count/$this->limit);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;
        
        $sdf_items = self::getItems($sdf['items'],$cur_page,$this->limit);
        
        if ($sdf_items){
            $offset = ($cur_page-1) * $this->limit + 1;
            $items = array();
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
                );
                $offset++;
            }
        }
        $wms_order_code = $stockin_bn;
        $create_time = preg_match('/-|\//',$sdf['create_time']) ? $sdf['create_time'] : date("Y-m-d H:i:s",$sdf['create_time']);
        $order_type = $this->__stockin_type[$sdf['io_type']] ? $this->__stockin_type[$sdf['io_type']] : 'IN_OTHER';
        $params = array(
            'uniqid' => $sdf['batch_id'],
            'out_order_code' => $stockin_bn,
            'order_type' => $order_type,
            'created' => $create_time,
            'wms_order_code' => $wms_order_code,
            'is_finished' => $cur_page >= $total_page ? 'true' : 'false',
            'current_page' => $cur_page,// 当前批次,用于分批同步
            'total_page' => $total_page,// 总批次,用于分批同步
            'logistics_code' => '',// TODO: 快递公司（如果是汇购传递快递公司，则该项目不能为空，否则可以为空处理）
            'logistics_no' => '',// TODO: 运输公司运单号
            'remark' => $sdf['memo'],
            'shipper_name' => $sdf['shipper_name'] ? $sdf['shipper_name'] : '',
            'shipper_zip' => $sdf['shipper_zip'] ? $sdf['shipper_zip'] : '200000',// TODO: 收货人邮政编码
            'shipper_state' => $sdf['shipper_state'] ? $sdf['shipper_state'] : '',// TODO: 退货人所在省
            'shipper_city' => $sdf['shipper_city'] ? $sdf['shipper_city'] : '',// TODO: 退货人所在市
            'shipper_district' => $sdf['shipper_district'] ? $sdf['shipper_district'] : '',// TODO: 退货人所在县（区），注意有些市下面是没有区的

            'shipper_address' => $sdf['shipper_address']  ? $sdf['shipper_address'] : '',// TODO: 收货地址（出库时非空）
            'shipper_phone' => $sdf['shipper_phone']  ? $sdf['shipper_phone'] : '',// TODO: 收货人电话号码（如有分机号用“-”分隔）(电话和手机必选一项) 
            'shipper_mobile' => $sdf['shipper_mobile']  ? $sdf['shipper_mobile'] : '',// TODO: 收货人手机号码(电话和手机必选一项) 
            'shipper_email ' => $sdf['shipper_email']  ? $sdf['shipper_email'] : '',// TODO: 收货人手机号码(电话和手机必选一项) 
            'total_goods_fee' => $sdf['total_goods_fee'],// 订单商品总价（精确到小数点后2位）

            'line_total_count' => $sdf['line_total_count'],// TODO: 订单行项目数量
            'item_total_num' => $sdf['item_total_num'],
            'storage_code' => $sdf['storage_code'],// 库内存放点编号
            'items' => json_encode(array('item'=>$items)),
            'expect_end_time'=>date('Y-m-d H:i:s'),
            'platform_order_code'=>'',
            'source_id'=>$sdf['supplier_bn'] ? $sdf['supplier_bn'] : 'SHP_V1',
        );
       return $params;
    }

     /**
     * 入库单取消参数
     * @param   array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockin_cancel_params($sdf)
    {
        $stockin_bn = $sdf['io_bn'];
        $order_type = $this->__stockin_type[$sdf['io_type']] ? $this->__stockin_type[$sdf['io_type']] : 'IN_OTHER';
        $params = array(
            'out_order_code' => $stockin_bn,
            //'order_type' => $order_type,
            'uniqid' => self::uniqid(),
        );
        return $params;
    }

    /**
     * 入库单查询参数
     * @param  
     * @return  
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockin_search_params($sdf)
    {
        $params = array(
            'out_order_code'=>$sdf['stockin_bn'],    
        );

        return $params;
    }
}