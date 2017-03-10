<?php
/**
* 退货单
*
* @copyright shopex.cn 2014.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_360buy_reship extends middleware_wms_matrixwms_request_reship{

   
    /**
     * 退货单添加退货退知参数
     *@access protected
    * @param array
    */

    protected function _getReship_create_params($sdf)
    {
        
        $reship_bn = $sdf['reship_bn'];
        $oForeign_sku = app::get('console')->model('foreign_sku');
        $branch_relationObj = app::get('wmsmgr')->model('branch_relation');
        $branch_relation = $branch_relationObj->dump(array('sys_branch_bn'=>$sdf['branch_bn']));
        $items_count = count($sdf['items']);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;
        $wms_id = kernel::single('ome_branch')->getWmsId($sdf['branch_bn']);
        if ($sdf['items']){
            $offset = 0;
            $items = array();
            foreach ($sdf['items'] as $v){
                // 获取外部商品sku
                $items[] = array(
                    'item_code' => $oForeign_sku->get_product_outer_sku( $wms_id,$v['bn'] ),
                    'item_name' => $v['name'],
                    'item_quantity' => $v['num'],
                    'item_price' => $v['price'] ? $v['price'] : '0',// TODO: 商品价格
                    'item_line_num' => $offset,// TODO: 订单商品列表中商品的行项目编号，即第n行或第n个商品
                    'trade_code' => '',//可选(若是淘宝交易订单，并且不是赠品，必须要传订单来源编号) 
                    'item_id' => $outer_sku,// 商品ID
                    'is_gift' => '0',// TODO: 判断是否为赠品0:不是1:是
                    'item_remark' => '',// TODO: 商品备注
                    'inventory_type' => '1',// TODO: 库存类型1可销售库存101类型用来定义残次品201冻结类型库存301在途库存
                );
                $offset++;
            }
        }

        $params = array(
            'uniqid' => self::uniqid(),
            'wms_supplier' => '',//    TODO: 服务提供商编号
            'out_order_code' => $reship_bn,
            'warehouse_code' => $branch_relation['wms_branch_bn'],
            'orig_order_code' => $sdf['original_delivery_bn'],
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
            'expect_start_time'=>date('Y-m-d H:i:s'),//预计收货时间
            'approver'=>'admin',
        );
        return $params;
    }

    /**
     * 退货查询参数
     * @param 
     * @return 
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getReship_search_params($sdf)
    {
        $params = array(
            'out_order_code' =>$sdf['out_order_code'],   
        );
        
        return $params;
    }

    public function reship_create_callback($result,$callback_params){
        #调用用户callback
        $this->callUserCallback($callback_params);
        
        #更新日志状态信息
        $addon = $data = array();
        $msg_code = $status = $msg = '';
        $msg_code = $result['res'];
        $msg = $result['err_msg'].'('.serialize($result).')';
        $status = $result['rsp'];
        $data = $result['data'];
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
                $reshipObj = app::get('ome')->model('reship');
                $reship_bn = $log_detail['original_bn'];
                $out_iso_bn = $data['wms_order_code'];
                $reshipObj->db->exec("UPDATE sdb_ome_reship SET out_iso_bn='".$out_iso_bn."' WHERE reship_bn='".$reship_bn."'");
            }
        }else{
            $res = 'log_id is not empty';
        }

        return array('rsp'=>$rsp, 'res'=>$res, 'msg_id'=>$msg_id, 'log_id'=>$log_id);
    }
}