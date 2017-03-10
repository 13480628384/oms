<?php
/**
* 出库单
*
* @copyright shopex.cn 2014.05.07
* @author sunjing@shopex.cn
*/
class middleware_wms_matrixwms_request_360buy_stockout extends middleware_wms_matrixwms_request_stockout{

    /**
    * 出库通知
    * @access public
    * @param Array $sdf 出库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockout_create(&$sdf,$sync=false,$cur_page='1',$log_id=''){

        $stockout_bn = $sdf['io_bn'];

        // 状态判断,出库单状态为取消，则不发起同步
        if ( $this->iscancel($stockout_bn,'stockout') ){
            return $this->msgOutput('succ','出库单已取消,终止同步');
        }

        $items_count = count($sdf['items']);
        $total_page = ceil($items_count/$this->limit);
        $sdf['batch_id'] = substr(self::uniqid(),0,25);
        for ($page=1;$page<=$total_page;$page++)
        {
            $params = $this->_getStockout_create_params($sdf,$page);

            $adapter_callback = array(
                'class' => get_class($this),
                'method' => 'stockout_create_callback',
            );
            $writelog = array(
                'log_id' => $log_id,
                'log_title' => '出库单添加',
                'log_type' => 'store.trade.stockout',
                'original_bn' => $stockout_bn,
                'original_params' => serialize($sdf),
            );
            $method = 'store.wms.outorder.create';

            $result = $this->request($method,$params,$writelog,'false',$adapter_callback);
            
            return $result;
        }
        
    }
    
    
    
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
        $oForeign_sku = app::get('console')->model('foreign_sku');
        $branch_relationObj = app::get('wmsmgr')->model('branch_relation');
        $branch_relation = $branch_relationObj->dump(array('sys_branch_bn'=>$sdf['branch_bn']));
        if ($sdf_items){
            $items = array();
            $offset = ($cur_page-1) * $this->limit+1;
            $inventory_type = '1';
            if (isset($sdf['type_id']) && $sdf['type_id']) {

                $inventory_type = $this->inventory_type[$sdf['type_id']] ? $this->inventory_type[$sdf['type_id']] : '1';
            }
            $wms_id = kernel::single('ome_branch')->getWmsId($sdf['branch_bn']);
            foreach ($sdf_items as $v){
                $items[] = array(
                    'item_code' => $oForeign_sku->get_product_outer_sku( $wms_id,$v['bn'] ),
                    'item_name' => $v['name'],
                    'item_quantity' => $v['num'],
                    'item_price' => $v['price'] ? $v['price'] : '0',// TODO: 商品价格
                    'item_line_num' => $offset,// TODO: 订单商品列表中商品的行项目编号，即第n行或第n个商品
                    'trade_code' => '',//可选(若是淘宝交易订单，并且不是赠品，必须要传订单来源编号) 
                    'item_id' => $v['bn'],// 商品ID
                    'is_gift' => '0',// TODO: 判断是否为赠品0:不是1:是
                    'item_remark' => '',// TODO: 商品备注
                    'inventory_type' => $inventory_type,// TODO: 库存类型1可销售库存101类型用来定义残次品201冻结类型库存301在途库存
                );
                $offset++;
            }
        }
        $wms_order_code = $stockout_bn;
        $order_type = $this->stockout_type[$sdf['io_type']] ? $this->stockout_type[$sdf['io_type']] : 'OUT_OTHER';
        $create_time = preg_match('/-|\//',$sdf['create_time']) ? $sdf['create_time'] : date("Y-m-d H:i:s",$sdf['create_time']);

        $params = array(
            'uniqid' => self::uniqid(),
            'customer_id' => '',// 客户编码
            'out_order_code' => $stockout_bn,
            'order_type' => $order_type,
            'created' => $create_time,
            'wms_order_code' => $wms_order_code,
            'is_finished' => $cur_page >= $total_page ? 'true' : 'false',
            'current_page' => $cur_page,// 当前批次,用于分批同步
            'total_page' => $total_page,// 总批次,用于分批同步
            'shipping_type' => 'EXPRESS',// TODO: 运输方式 EXPRESS-快递 EMS-邮政速递
            'logistics_code' => $sdf['wms_logi_code'],// TODO: 快递公司（如果是汇购传递快递公司，则该项目不能为空，否则可以为空处理）
            'remark' => $sdf['memo'] ? $sdf['memo'] : '没有也得传啊',
            'memo' => $sdf['memo'] ? $sdf['memo'] : '没有也得传啊',
            'total_amount' => $sdf['total_goods_fee'],// 订单商品总价（精确到小数点后2位）
            'receiver_name' => $sdf['receiver_name']  ? $sdf['receiver_name'] : '未知',
            'receiver_zip' => $sdf['receiver_zip'] ? $sdf['receiver_zip'] : '232122',// TODO: 收货人邮政编码
            'receiver_state' => $sdf['receiver_state'] ? $sdf['receiver_state'] : '未知',// TODO: 退货人所在省
            'receiver_city' => $sdf['receiver_city'] ? $sdf['receiver_city'] : '未知',// TODO: 退货人所在市
            'receiver_district' => $sdf['receiver_district'] ? $sdf['receiver_district'] : '未知',// TODO: 退货人所在县（区），注意有些市下面是没有区的
            'receiver_address' => $sdf['receiver_address'] ? $sdf['receiver_address'] : '未知',// TODO: 收货地址（出库时非空）
            'receiver_phone' => $sdf['receiver_phone'] ? $sdf['receiver_phone'] : '未知',// TODO: 收货人电话号码（如有分机号用“-”分隔）(电话和手机必选一项) 
            'receiver_mobile' => $sdf['receiver_mobile'] ? $sdf['receiver_mobile'] : '未知',// TODO: 收货人手机号码(电话和手机必选一项) 
            'receiver_email ' => $sdf['receiver_email'] ? $sdf['receiver_email'] : '未知',// TODO: 收货人手机号码(电话和手机必选一项) 
            'receiver_time' => '',
            'sign_standard' => '',// TODO: 签收标准（如：身仹证150428197502205130）
            'source_plan' => '',// TODO: 来源计划点
            'line_total_count' => $sdf['line_total_count'],// TODO: 订单行项目数量
            'item_total_num' => $sdf['item_total_num'],
            'storage_code' => $sdf['storage_code'],// 库内存放点编号
            'warehouse_code'=>$branch_relation['wms_branch_bn'],
            'items' => json_encode(array('item'=>$items)),
            'expect_start_time'=>date('Y-m-d H:i:s'),//过期时间
             'total_trade_fee' => $sdf['total_goods_fee'],//总金额
             'is_cod'=>$sdf['is_cod'] ? $sdf['is_cod'] : 'false',
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
            'out_order_code' => $sdf['out_iso_bn'],
            'order_type' => $order_type,
        );
        return $params;
    }

    /**
     * 出库单查询
     * @param   
     * @return  
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockout_search_params($sdf)
    {
        $params = array(
            'out_order_code' =>$sdf['out_order_code'],   
        );

        return $params;
    }

    /**
    * 出库单取消
    * @access public
    * @param Array $sdf 出库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockout_cancel(&$sdf,$sync=false){
        return array('rsp'=>'fail','msg'=>'接口方法不存在','msg_code'=>'w402');
    }

    
    /**
     * 出库单创建异步响应.
     * @param
     * @return 
     * @access  public
     * @author sunjing@shopex.cn
     */
    public function stockout_create_callback($result,$callback_params)
    {
        
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
                $out_iso_bn = $data['wms_order_code'];
                $order_type = $params['order_type'];
                $original_bn = $log_detail['original_bn'];
                $db = kernel::database();
                if ($order_type == 'OUT_PURCHASE_RETURN') {
                    $db->exec("UPDATE sdb_purchase_returned_purchase SET out_iso_bn='".$out_iso_bn."' WHERE rp_bn='".$original_bn."'");
                }else{
                    $db->exec("UPDATE sdb_taoguaniostockorder_iso SET out_iso_bn='".$out_iso_bn."' WHERE iso_bn='".$original_bn."'");
                }
                
            }
        }else{
            $res = 'log_id is not empty';
        }
        
        return array('rsp'=>$rsp, 'res'=>$res, 'msg_id'=>$msg_id, 'log_id'=>$log_id);
    }
}