<?php
/**
* 发货单
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_matrixwms_request_delivery extends middleware_wms_matrixwms_request_common{

    /**
    * 发货通知
    * @access public
    * @param Array $sdf 发货单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function delivery_create(&$sdf,$sync=false){

        $delivery_bn = $sdf['outer_delivery_bn'];

        // 状态判断,发货单状态为取消，则不发起同步
        if ( $this->iscancel($delivery_bn,'delivery') ){
            return $this->msgOutput('success','发货单已取消,终止同步');
        }
        

        $params = $this->__getDelivery_create_params($sdf);
        if (!$params) {
            return $this->msgOutput('fail','发货单同步失败,获取面单号失败');
        }
        $adapter_callback = array(
            'class' => get_class($this),
            'method' => 'delivery_create_callback',
            'params'=>array('delivery_bn'=>$sdf['outer_delivery_bn']),
        );
        $writelog = array(
            'log_title' => '发货单添加',
            'log_type' => 'store.trade.delivery',
            'original_bn' => $delivery_bn,
        );
        $method = 'store.wms.saleorder.create';

        return $this->request($method,$params,$writelog,$sync,$adapter_callback);
    }

    /**
    * 发货通知callback接收
    * @access public
    * @param Array $callback_result 发货单callback结果(标准的msgOutput:middleware_message)
    * @param Array $callback_params adapter_callback参数
    * @return Array 标准输出格式
    */
    public function delivery_create_callback($callback_result,$callback_params){
        $rsp = $this->callback($callback_result,$callback_params);
        $status = $callback_result['rsp'];
        if($status == 'succ'){
            $api_status = 'success';
        }else{
            $api_status = 'fail';
        }
        $log_id = $callback_params['log_id'];
        $log_detail = $this->getLogDetail(array('log_id'=>$log_id), 'msg_id,params,original_bn,addon');
        $delivery_bn = $log_detail['original_bn'];
        if ($delivery_bn) {
            $deliveryObj = app::get('ome')->model('delivery');
            $deliverys = $deliveryObj ->dump(array('delivery_bn'=>$delivery_bn),'delivery_id');
            $msg = $callback_result['err_msg'] ? $callback_result['err_msg'] : $callback_result['res'];
            app::get('console')->model('delivery_send')->update_send_status($deliverys['delivery_id'],$api_status,$msg);

        }
        
        return $rsp;
    }

    /**
    * 发货单取消
    * @access public
    * @param Array $sdf 发货单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function delivery_cancel(&$sdf,$sync=false){

        $delivery_bn = $sdf['outer_delivery_bn'];
        
        $params =$this->_getDelivery_cancel_params($sdf);

        $writelog = array(
            'log_title' => '发货单取消',
            'log_type' => 'store.trade.delivery',
            'original_bn' => $delivery_bn,
        );
        $method = 'store.wms.saleorder.cancel';

        return $this->request($method,$params,$writelog,$sync);
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
        );
        return $params;
    }
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

        if ($delivery_items){
            $items = array();
            $offset = 1;
            foreach ($delivery_items as $v){
                $items[] = array(
                    'item_code' => $v['bn'],
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
            'receiver_name' => $sdf['consignee']['name'],
            'receiver_zip' => $sdf['consignee']['zip'] ? $sdf['consignee']['zip'] : '231201',
            'receiver_phone' => $sdf['consignee']['telephone'],
            'receiver_mobile' => $sdf['consignee']['mobile'],
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
            'dispatch_time' => $sdf['delivery_time']
        );
        return $params;
    }

    /**
     * 发货单查询
     * @param  
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    function delivery_search($sdf=array(),$sync=false)
    {
        $delivery_bn = $sdf['delivery_bn'];

        $params = $this->__getDelivery_search_params($sdf);
        $writelog = array(
            'log_title' => '发货单查询',
            'log_type' => 'store.trade.delivery',
            'original_bn' => $delivery_bn,
        );
        $method = 'store.wms.saleorder.get';
        
        return $this->request($method,$params,$writelog,$sync);
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
            'out_order_code'=>$sdf['out_order_code'],    
        );
        return $params;
    }
    
    /**
     * RPC同步返回数据接收
     * @access public
     * @param json array $res RPC响应结果
     * @param array $params 同步日志ID
     */
    public function response_log($res, $params){
        parent::response_log($res, $params);
        $response = json_decode($res, true);

        if (is_array($response)){
            $status = $response['rsp'];
            $msg_id = $response['msg_id'];
        }else{
            $status = 'fail';
        }
        $delivery_bn = $params['callback_params']['delivery_bn'];
        if ($delivery_bn) {
            $deliveryObj = app::get('ome')->model('delivery');
            $deliverys = $deliveryObj ->dump(array('delivery_bn'=>$delivery_bn),'delivery_id');
            $msg = $response['err_msg'] ? $response['err_msg'] : $response['res'];
            app::get('console')->model('delivery_send')->update_send_status($deliverys['delivery_id'],$status,$msg);

        }
    }

    
    /**
     * 获取京东面单号
     * @param   
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    function _get_waybil($waybillparams)
    {
        $rs = array('rsp'=>'fail');
        $channel = $waybillparams['channel'];
        $jdAccount = explode('|||', $channel['shop_id']);
        if ($channel && $jdAccount[0]) {
            $jdObj = kernel::single('logisticsmanager_waybill_360buy');
            $rpcData = array(
                'preNum' => 1,
                'customerCode' => $jdAccount[0],
                'delivery_id' => $waybillparams['delivery_id'],
                'delivery_bn'=>$waybillparams['delivery_bn'],
                'businessType' => $jdObj->businessType($channel['logistics_code']),
                'shop_id'=>$jdAccount[1],
                'channel_id'=>$channel['channel_id'],
                'logistics_code'=>$channel['logistics_code'],
            );

            $jdRpcObj = kernel::single('logisticsmanager_rpc_request_360buy');
            $result = $jdRpcObj->setChannelType('360buy')->get_waybill_number($rpcData);

        }
       
        if ($result['rsp'] == 'succ' && $result['data'][0]['logi_no']) {
            $rs=array(
                'rsp'=>$result['rsp'],
                'logi_no'=>$result['data'][0]['logi_no'],
            );
            $db = kernel::database();
            $db->exec("UPDATE sdb_ome_delivery SET logi_no='".$rs['logi_no']."' WHERE delivery_id=".$waybillparams['delivery_id']);
        }
        return $rs;

    }
    
    
    /**
     * 获取物流公司信息
     * @param   
     * @return 
     * @access  public
     * @author sunjing@shopex.cn
     */
    function getChannelinfo($logi_id)
    {
        $db = kernel::database();
        $channel = $db->selectrow("SELECT c.channel_id,c.channel_type,c.logistics_code,c.shop_id FROM sdb_ome_dly_corp as d LEFT JOIN sdb_logisticsmanager_channel as c on d.channel_id=c.channel_id WHERE d.corp_id=".$logi_id." AND c.status='true'");

        if ($channel && in_array($channel['channel_type'],array('360buy'))) {
            return $channel;
        }
    }
}