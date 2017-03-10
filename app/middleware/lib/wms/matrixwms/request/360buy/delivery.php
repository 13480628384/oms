<?php
/**
* 发货单
*
* @copyright shopex.cn 2014.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_360buy_delivery extends middleware_wms_matrixwms_request_delivery{

    /**
     * 发货参数
     * @param  array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function __getDelivery_create_params($sdf){
        
        $delivery_bn = $sdf['outer_delivery_bn'];
        $delivery_items = $sdf['delivery_items'];
        $items_count = count($delivery_items);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;
        $oForeign_sku = app::get('console')->model('foreign_sku');
        $wms_id = kernel::single('ome_branch')->getWmsId($sdf['branch_bn']);
        $branch_relationObj = app::get('wmsmgr')->model('branch_relation');
        $branch_relation = $branch_relationObj->dump(array('sys_branch_bn'=>$sdf['branch_bn']));
        if ($delivery_items){
            $items = array();
            $offset = 1;
            foreach ($delivery_items as $v){
                $items[] = array(
                    'item_code' => $oForeign_sku->get_product_outer_sku( $wms_id,$v['bn'] ),
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
        $params = array(
            'uniqid' => self::uniqid(),
            'out_order_code' => $delivery_bn,
            'order_source' => $sdf['shop_type'] ? strtoupper($sdf['shop_type']) : 'OTHER',
            'shipping_type' => 'EXPRESS',
            'shipping_fee' => $sdf['logistics_costs'],
            'platform_order_code' => $sdf['order_bn'],
            'logistics_code' => $sdf['wms_logi_code'],
            'shop_code' => $sdf['shop_code'],
            'remark' => $sdf['memo'],//订单上的客服备注
            'created' => $create_time,
            'wms_order_code' => $sdf['order_bn'],//暂时修改成订单号
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
            'is_cod' => $sdf['is_cod'] ? $sdf['is_cod'] : 'false',//是否货到付款。可选值:true(是),false(否)
            'cod_fee' => $sdf['cod_fee'],//应收货款（用于货到付款）
            'cod_service_fee' => '0',//cod服务费（货到付款 必填）
            'total_goods_fee' => $sdf['total_goods_amount']-$sdf['goods_discount_fee'],//商品原始金额-商品优惠金额
            'total_trade_fee' => $sdf['total_amount'],//订单交易金额
            'receiver_name' => $sdf['consignee']['name'],
            'receiver_zip' => $sdf['consignee']['zip'],
            'receiver_phone' => $sdf['consignee']['telephone'] ? $sdf['consignee']['telephone'] : '13222222222',
            'receiver_mobile' => $sdf['consignee']['mobile'] ? $sdf['consignee']['mobile'] : '333333',
            'receiver_state' => $sdf['consignee']['province'],
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
            'dispatch_time' => $sdf['delivery_time'],
            'warehouse_code'=>$branch_relation['wms_branch_bn'],
            'memo'=>'不就备注嘛。我填呀',
            'expect_start_time'=>date('Y-m-d H:i:s'),//过期时间
        );
        return $params;
    }
    
    /**
     * 发货单取消
     * @param  
     * @return  
     * @access  public
     * @author cyyr24@sina.cn
     */
    protected function _getDelivery_cancel_params($sdf)
    {
        $oDelivery_ext = app::get('console')->model('delivery_extension');
        $delivery_ext = $oDelivery_ext->dump(array('delivery_bn'=>$sdf['outer_delivery_bn']),'original_delivery_bn');
        
        $params = array(
            'warehouse_code' => $sdf['branch_bn'],
            'out_order_code' => $delivery_ext['original_delivery_bn'],
        );
        return $params;
    }

    public function delivery_create_callback($result,$callback_params){
        
        
        #调用用户callback
        $this->callUserCallback($callback_params);
        
        #更新日志状态信息
        $addon = $data = array();
        $msg_code = $status = $msg = '';
        $msg_code = $result['res'];
        $msg = $result['err_msg'].'('.serialize($result).')';
        $status = $result['rsp'];
        $data = json_decode($result['data'],true);
        $msg_id = $result['msg_id'];

        if($status == 'succ'){
            $api_status = 'success';
            if ($data){
                $msg .= '('.serialize($data).')';
            }
        }else{
            $api_status = 'fail';
        }

        //错误编码
        $addon['msg_code'] = $msg_code;

        //错误等级
        if (isset($data['error_level']) && !empty($data['error_level'])){
            $addon['error_lv'] = $data['error_level'];
        }

        if ($status != 'succ' && $status != 'fail' ){
            $msg = 'rsp:'.$status .'res:'. $msg. 'data:'. $data;
            $res = 'rsp status : ' .$status. ' incorrect';
        }
        
        $log_id = $callback_params['log_id'];
        $log_detail = $this->getLogDetail(array('log_id'=>$log_id), 'msg_id,params,original_bn,addon');
        $params = unserialize($log_detail['params']);
        if ($log_id){
            if (!$log_detail['msg_id']){
                $addon['msg_id'] = $msg_id;
            }
            $this->updateLog($log_id,$msg,$api_status,'',$addon);
            $rsp = 'succ';
            if ($status == 'succ') {
                $oDelivery_extension = app::get('console')->model('delivery_extension');
                $ext_data['delivery_bn'] = $log_detail['original_bn'];
                $ext_data['original_delivery_bn'] = $data['wms_order_code'];
                $oDelivery_extension->save($ext_data);
            }
        }else{
            $res = 'log_id is not empty';
        }
        
        return array('rsp'=>$rsp, 'res'=>$res, 'msg_id'=>$msg_id, 'log_id'=>$log_id);
        

        
    }
}