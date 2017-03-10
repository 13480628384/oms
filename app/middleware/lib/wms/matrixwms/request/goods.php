<?php
/**
* 商品
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_matrixwms_request_goods extends middleware_wms_matrixwms_request_common{

    /**
    * 商品通知
    * @access public
    * @param Array $sdf 商品数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function goods_add(&$sdf,$sync=false){
        $product_ids = array();
        $params = $this->__get_params($sdf,$product_ids);

        $adapter_callback = array(
            'class' => __CLASS__,
            'method' => 'goods_add_callback',
        );
        $writelog = array(
            'log_title' => '商品添加',
            'log_type' => 'store.trade.goods',
            'addon' => $product_ids,
        );
        $method = 'store.wms.item.add';

        return $this->request($method,$params,$writelog,$sync,$adapter_callback);
    }

    /**
    * 商品通知callback接收
    * @access public
    * @param Array $callback_result 商品callback结果(标准的msgOutput:middleware_message)
    * @param Array $callback_params adapter_callback参数
    * @return Array 标准输出格式
    */
    public function goods_add_callback($callback_result,$callback_params){
        
        $status = $callback_result['rsp'];
        $data = $callback_result['data'];
        $msg_id = $callback_result['msg_id'];
        $msg = $callback_result['err_msg'];
        $msg_code = $callback_result['res'];
        //第三方仓储无此接口时,商品同步状态设为成功
        if ($msg == 'w03020' || $msg_code == 'w03020'){
            $status = 'succ';
            $msg = '仓储物流系统无此接口';
        }
        $data = json_decode($data,true);

        //$oApi_log = app::get('omeapilog')->model('api_log');
        $log_id = $callback_params['log_id'];
        $log_detail = $this->getLogDetail(array('log_id'=>$log_id), 'params,addon');
        
        $params = unserialize($log_detail['params']);
        $node_id = $params[1]['to_node_id'];
        $wms_id = $this->getWmsIdByNodeId($node_id);
        $sync_product_id = $log_detail['addon'];
        if (!is_array($sync_product_id)) {
            $sync_product_id = unserialize($sync_product_id);
        }
        // 更新成功的货品同步状态
        $succ_product_ids = array();
        if (isset($data['succ']) && $data['succ']){
            $sync_sku_data = array();
            
            foreach ( $data['succ'] as $sku ){
                $sync_sku_data[] = array(
                    'inner_sku' => $sku['item_code'],
                    'outer_sku' => $sku['wms_item_code'],
                    'status' => '3',
                );
                //查询product_id
                $sql = 'SELECT `product_id` FROM `sdb_ome_products` WHERE `bn`=\''.$sku['item_code'].'\'';
                $tmp = kernel::database()->selectrow($sql);
                $succ_product_ids[] = $tmp['product_id'];
            }
        }

        // 无外部sku编号,更新所有商品同步状态为已同步
        if ($status == 'succ'){
            if (is_array($sync_product_id) && $sync_product_id){
                $sql = "UPDATE `sdb_console_foreign_sku` SET `new_tag`='1',`sync_status`='3' WHERE  `inner_sku` IN (SELECT `bn` FROM `sdb_ome_products` WHERE `product_id` IN (".implode(',', $sync_product_id).")) AND `wms_id`='".$wms_id."' ";
                
                kernel::database()->exec($sql);
            }
        }else{
            //更新部分失败的货品同步状态
            $error_flag = false;
            $fail_product_ids = array();
            if (isset($data['error']) && $data['error']){
                $error_info = $exists_product_ids = array();
                $all_exists_flag = false;
                foreach ( $data['error'] as $sku ){
                    if ($sku['error_code'] != 'w03109'){// 商品已存在
                        $error_flag = true;
                        $all_exists_flag = false;
                        $sync_sku_data[] = array(
                            'inner_sku' => $sku['item_code'],
                            'outer_sku' =>$sku['wms_item_code'],
                            'status' => '1',
                        );
                        //查询product_id
                        $sql = 'SELECT `product_id` FROM `sdb_ome_products` WHERE `bn`=\''.$sku['item_code'].'\'';
                        $tmp = kernel::database()->selectrow($sql);
                        $fail_product_ids[] = $tmp['product_id'];

                        if ($sku['item_code']){
                            $error_info[] = $sku['item_code'].':'.$sku['error_description'];
                        }
                    }else{
                        $all_exists_flag = true;
                        //查询已存在的product_id
                        $sql = 'SELECT `product_id` FROM `sdb_ome_products` WHERE `bn`=\''.$sku['item_code'].'\'';
                        $tmp = kernel::database()->selectrow($sql);
                        $exists_product_ids[] = $tmp['product_id'];
                    }
                }
            }
            // data返回结果中的error部分或data为空,更新所有商品同步状态为未同步
            if ($status == 'fail' && $error_flag == false && !$succ_product_ids){
                if (is_array($sync_product_id) && $sync_product_id){
                    $sql = "UPDATE `sdb_console_foreign_sku` SET `sync_status`='0' WHERE  `inner_sku` IN (SELECT `bn` FROM `sdb_ome_products` WHERE `product_id` IN (".implode(',', $sync_product_id).")) AND `wms_id`='".$wms_id."' ";
                    kernel::database()->exec($sql);
                }
            }else{
                //data 不为空，则更新遗漏的(非成功)失败货号状态
                $remain_product_ids = array_diff($sync_product_id,$succ_product_ids);
                if ($diff_product_ids = array_diff($remain_product_ids,$fail_product_ids)){
                    $sql = "UPDATE `sdb_console_foreign_sku` SET `sync_status`='0' WHERE  `inner_sku` IN (SELECT `bn` FROM `sdb_ome_products` WHERE `product_id` IN (".implode(',', $diff_product_ids).")) AND `wms_id`='".$wms_id."' ";
                    kernel::database()->exec($sql);
                }
            }
            //更新已存在商品的状态为已同步
            if ($exists_product_ids){
                $sql = "UPDATE `sdb_console_foreign_sku` SET `sync_status`='3' WHERE  `inner_sku` IN (SELECT `bn` FROM `sdb_ome_products` WHERE `product_id` IN (".implode(',', $exists_product_ids).")) AND `wms_id`='".$wms_id."' ";
                kernel::database()->exec($sql);
            }
        }
        if ($sync_sku_data){
            
            $this->set_sync_status($sync_sku_data,$node_id);
        }

        if( $status == 'succ' || ($status == 'fail' && $all_exists_flag == true) ){
            $api_status = 'success';
            $msg .= '('.serialize($data).')';
        }else{
            $api_status = 'fail';
            if ($error_info){
                $msg = '同步失败商品('.implode(',', $error_info).") - ". serialize($data).',错误消息:'.$msg;
            }else{
                $msg .= '('.serialize($data).')';
            }
        }
        
        $this->updateLog($log_id,$msg,$api_status);
        return array('rsp'=>'succ', 'msg'=>'处理成功', 'msg_id'=>$msg_id, 'log_id'=>$log_id);
    }

    /**
    * 商品编辑
    * @access public
    * @param Array $sdf 商品数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function goods_update(&$sdf,$sync=false){

        $params = $this->__get_params($sdf,$product_ids);

        $adapter_callback = array(
            'class' => __CLASS__,
            'method' => 'goods_add_callback',
        );
        $writelog = array(
            'log_title' => '商品编辑',
            'log_type' => 'store.trade.goods',
            'addon' => $product_ids,
        );
        $method = 'store.wms.item.update';

        return $this->request($method,$params,$writelog,$sync,$adapter_callback);
    }

    /**
     * 商品同步参数
     * @param  array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function __get_params(&$sdf,&$product_ids=array()){
        $node_id = $this->node_id;
        $params = $items = array();
        if (is_array($sdf) && $sdf){
            $product_ids = array();
            foreach ($sdf as $p){
                if (!is_array($p)) continue;
                $product_ids[] = $p['product_id'];
                $spec_info = preg_replace(array('/：/','/、/'),array(':',';'),$p['property']);
                $items[] = array(
                    'name' => $p['name'],
                    'title' => $p['name'],// 商品标题
                    'item_code' => $p['bn'],
                    'remark' => '',//商品备注
                    'type' => 'NORMAL',
                    'is_sku' => '1',
                    'gross_weight' => $p['weight'] ? $p['weight'] : '',// 毛重,单位G
                    'net_weight' => $p['weight'] ? $p['weight'] : '',// 商品净重,单位G
                    'tare_weight' => '',// 商品皮重，单位G
                    'is_friable' => '',// 是否易碎品
                    'is_dangerous' => '',// 是否危险品
                    //'weight' => $p['weight'] ? $p['weight'] : '0',
                    //'length' => '0.00',// 商品长度，单位厘米
                    //'width' => '0.00',// 商品宽度，单位厘米
                    //'height'=> '0.00',// 商品高度，单位厘米
                    //'volume'=> '0.00',// 商品体积，单位立方厘米
                    'pricing_cat' => '',// 计价货类
                    'package_material' => '',// 商品包装材料类型
                    'price' => '',
                    'support_batch' => '否',
                    'support_expire_date' => '否',
                    'expire_date' => date('Y-m-d'),
                    'support_barcode' => '0',
                    'barcode' => $p['barcode'] ? $p['barcode'] : '',
                    'support_antifake' => '否',
                    'unit' => $p['unit'] ? $p['unit'] : '',
                    'package_spec' => '',// 商品包装规格
                    'ename' => '',// 商品英文名称
                    'brand' => '',
                    'batch_no' => '',
                    'goods_cat' => '',// 商品分类
                    'color' => '',// 商品颜色
                    'property' => $spec_info,//规格
                );
            }
        }
        $params['item_lists'] = json_encode(array('item'=>$items));
        $params['uniqid'] = self::uniqid();
        return $params;
    }

}