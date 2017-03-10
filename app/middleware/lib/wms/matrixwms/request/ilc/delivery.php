<?php
/**
* 发货单
*
* @copyright shopex.cn 2014.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_ilc_delivery extends middleware_wms_matrixwms_request_delivery{

    /**
     * 发货参数
     * @param  array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function __getDelivery_create_params($sdf){
        $sdf['logistics_no'] = $sdf['logi_no'];
        //判断是否京东面单单子,需要获取运单号
        $channel = $this->getChannelinfo($sdf['logi_id']);
        if ($channel && $sdf['logi_no']=='') {

            $waybillparams = array(
                'delivery_id' => $sdf['delivery_id'],
                'delivery_bn'=>$sdf['outer_delivery_bn'],
                'channel' => $channel,    
            );
            $rs =$this->_get_waybil($waybillparams);
            if ($rs['rsp'] == 'fail') {
                $sendObj = app::get('console')->model('delivery_send');
                $msg = $rs['res'] ? $rs['res'] : $rs['err_msg'];
                $sendObj->update_send_status($sdf['delivery_id'],'fail',$rs['res']);
                return false;
            }
            if ($rs['logi_no']) {
                $sdf['logistics_no'] = $rs['logi_no'];

            }
        }
        $delivery_bn = $sdf['outer_delivery_bn'];
        $delivery_items = $sdf['delivery_items'];
        $items_count = count($delivery_items);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;
        if ($delivery_items){
            $items = array();
            $offset = 1;
            foreach ($delivery_items as $v){
                $barcode = $this->getBarcode($v['bn']);
                $items[] = array(
                    'item_code' => $barcode,
                    'item_name' => $v['product_name'],
                    'item_quantity' => $v['number'],
                    'item_price' => $v['price'],
                    'item_line_num' => $offset,// 订单商品列表中商品的行项目编号，即第n行或第n个商品
                    'trade_code' => $sdf['order_bn'],//可选(若是淘宝交易订单，并且不是赠品，必须要传订单来源编号) 
                    'item_id' => $v['bn'],// 外部系统商品sku
                    'is_gift' => $v['is_gift'] == 'ture' ? '1' : '0',// 是否赠品
                    'item_remark' => $v['memo'],// TODO: 商品备注
                    'inventory_type' => '1',// TODO: 库存类型1可销售库存101类型用来定义残次品201冻结类型库存301在途库存
                    'item_sale_price' => $v['sale_price']//成交额
                );
                $offset++;
            }
        }

        #发票信息
        if ($sdf['is_order_invoice'] == 'true' && $sdf['is_wms_invoice'] == 'true'){
            $invoice = $sdf['invoice'];
            $is_invoice = 'true';
            $invoice_type = $invoice_type_arr[$invoice['invoice_type']];
            $invoice_title = $invoice['invoice_title']['title'];
            
            #增值税抬头信息
            if ($invoice['invoice_type'] == 'increment'){
                $invoice_info = array(
                    'name' => $invoice['invoice_title']['uname'],
                    'phone' => $invoice['invoice_title']['tel'],
                    'address' => $invoice['invoice_title']['reg_addr'],
                    'taxpayer_id' => $invoice['invoice_title']['identify_num'],
                    'bank_name' => $invoice['invoice_title']['bank_name'],
                    'bank_account' => $invoice['invoice_title']['bank_account'],
                );
                $invoice_info = json_encode($invoice_info);
            }
            
            #发票明细
            if ($invoice['invoice_items']){
                $invoice_items = array();
                $i_money = 0;
                foreach ($invoice['invoice_items'] as $val){
                    $price = round($val['money'],2);
                    $invoice_items[] = array(
                        'name' => $val['item_name'],
                        'spec' => $val['spec'],
                        'quantity' => $val['nums'],
                        'price' => $price,
                    );
                    $i_money += $price;
                }
            }
            if ($invoice['content_type'] == 'items'){
                $invoice_item = json_encode($invoice_items);
                $invoice_money = $i_money;
            }else{
                $invoice_desc = $invoice['invoice_desc'];
                $invoice_money = round($invoice['invoice_money'],2);
            }
        }
        
        $create_time = preg_match('/-|\//',$sdf['create_time']) ? $sdf['create_time'] : date("Y-m-d H:i:s",$sdf['create_time']);
        $order_type_arr = array('normal'=>'发货订单','360buy'=>'京东订单');
        $shop_code = isset($sdf['shop_code']) ? trim($sdf['shop_code']) : '';
        
        if (empty($shop_code)){
            $order_type = $order_type_arr[$sdf['shop_type']] ? $order_type_arr[$sdf['shop_type']] : $order_type_arr['normal'];
        }else{
            $order_type = $shop_code;
        }
        //$money = $is_cod == 'COD' ? $data['cod_fee'] : $data['cost_item'];
        
        $params = array(
            'order_type' => $order_type,
            'uniqid' => self::uniqid(),
            'out_order_code' => $delivery_bn,
            'order_source' => $sdf['shop_type'] ? strtoupper($sdf['shop_type']) : 'OTHER',
            'shipping_type' => $sdf['wms_logi_code'],//
            'shipping_fee' => $sdf['logistics_costs'],
            'platform_order_code' => $sdf['order_bn'],
            'logistics_code' => $sdf['wms_logi_code'],
            'logistics_no'=>$sdf['logistics_no'],
            'shop_code' => $sdf['shop_code'],
            'remark' => $sdf['memo'],//订单上的客服备注
            'created' => $create_time,
            'wms_order_code' => $delivery_bn,
            'is_finished' => 'true',
            'current_page' => 1,// 当前批次,用于分批同步
            'total_page' => 1,// 总批次,用于分批同步
            'has_invoice' => $is_invoice == 'true' ? 'true' : 'false',
            'invoice_type' => $invoice_type,
            'invoice_title' => $invoice_title,
            'invoice_fee' => $invoice_money,
            'invoice_info' => $invoice_info,
            'invoice_desc' => $invoice_desc,
            'invoice_item' => $invoice_item,
            'discount_fee' => $sdf['discount_fee'],
            'is_protect' => $sdf['is_protect'],
            'protect_fee' => $sdf['cost_protect'],
            'is_cod' => $sdf['is_cod'],//是否货到付款。可选值:true(是),false(否)
            'cod_fee' => $sdf['cod_fee'],//应收货款（用于货到付款）
            'cod_service_fee' => '0',//cod服务费（货到付款 必填）
            'total_goods_fee' => $sdf['total_goods_amount']-$sdf['goods_discount_fee'],//商品原始金额-商品优惠金额
            'total_trade_fee' => $sdf['total_amount'],//订单交易金额
            'receiver_name' => $sdf['consignee']['name'] ? $sdf['consignee']['name'] : '服务站',
            'receiver_zip' => '200000',
            'receiver_phone' => $sdf['consignee']['telephone'],
            'receiver_mobile' => $sdf['consignee']['mobile'],
            'receiver_state' => $sdf['consignee']['province'],
            'receiver_country'=>'中国',
            'receiver_city' => $sdf['consignee']['city'],
            'receiver_district' => $sdf['consignee']['district'],
            'receiver_address' => $sdf['consignee']['addr'],
            'receiver_email' => $sdf['consignee']['email'],
            'receiver_time' => $sdf['consignee']['r_time'],// TODO: 要求到货时间
            'line_total_count' => $sdf['line_total_count'],// TODO: 订单行项目数量
            'item_total_num' => $sdf['item_total_num'],
            'storage_code' => $sdf['storage_code'],// 库内存放点编号
            'items' => json_encode(array('item'=>$items)),
            'print_remark' => $sdf['print_remark'] ? json_encode($sdf['print_remark']) : '',
            'dispatch_time' => $sdf['delivery_time']
        );
       
        return $params;
    }

    /**
     * 发货单查询参数
     * @param
     * @return
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function __getDelivery_search_params($sdf)
    {
        $params = array(
            'out_order_code'=>$sdf['delivery_bn'],    
        );
        return $params;
    }

    /**
     * 发货单取消参数
     * @param   sdf array
     * @return  array
     * @access  public
     * @author sunjing@shopex.cn
     */
    protected  function _getDelivery_cancel_params($sdf)
    {
        $params = array(
            'warehouse_code' => $sdf['branch_bn'],
            'out_order_code' => $sdf['outer_delivery_bn'],
            'uniqid' => self::uniqid(),
        );
        return $params;
    }
}