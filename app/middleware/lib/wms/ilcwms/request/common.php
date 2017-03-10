<?php
/**
* 发起基类
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_ilcwms_request_common extends middleware_wms_abstract{

    /**
    * 设置节点号信息
    */
    public function setNodeId($node_id=''){
        $this->node_id = $node_id;
        $this->node_name = kernel::single('middleware_adapter')->getChannelNameByNodeId($node_id);
    }

    /**
     * API发起
     * 此方法对外平台接口发起请求
     * @access public
     * @param string $method API接口名称
     * @param Array $params 接口参数
     * @param mixed $node_id wms节点标识
     * @param Array $writelog 日志参数
     * @param bool $async true异常,false同步
     * @return boolean
     */
    public function request($method,$params,$writelog=array(),$sync=true){

        $params['to_node_id'] = $this->node_id;
        $log_title = $writelog['log_title'].'('.$this->node_name.')';
        $original_bn = $writelog['original_bn'];
        $log_type = $writelog['log_type'];
        $action_type = $writelog['action_type'];
        $addon['addon'] = $writelog['addon'];
        $addon['sync'] = $sync == true ? 'true' : 'false';

        $log_id = $this->getLogId();
        $retry_class = 'middleware_wms_ilcwms_request_common';
        $retry_method = 'rpc_request';
        $retry_params = array($method, $params, array('log_id'=>$log_id));
        $this->writeLog($log_id,$log_title,$retry_class,$retry_method,$retry_params,'','request','fail','',$addon,$log_type,$original_bn);

        return $this->rpc_request($method,$params,array('log_id'=>$log_id),$sync);
    }
    
    /**
     * RPC开始请求
     * 业务层数据过滤后，开始向上级框架层发起
     * @access public
     * @param string $method RPC远程服务接口名称
     * @param array $params 业务参数
     * @param Array $log 日志ID
     * @param bool $async 异常与同步
     * @return RPC响应结果
     */
    public function rpc_request($method,$params,$log,$sync=false){
        $node_id = $params['to_node_id'];
        
        $url = app::get('wmsmgr')->getConf('api_url'.$node_id);
        $time_out = !empty($time_out) ? $time_out : 5;
        $sys_params = array(
            'method' => $method,    
        );
        $params = array_merge($sys_params, (array)$params);
        $log_id = isset($log['log_id']) ? $log['log_id'] : $log;
        
            //商品日志重试,过滤已同步成功的货号
            if (in_array($method,array('store.wms.item.add','store.wms.item.update'))){
                
                if ($params['items']){
                    $succ_items = array();
                    $items = json_decode($params['items'],true);
                    
                    foreach($items as $key=>$v){
                        if ($this->issync($v['product_bn'],$node_id)){
                            $succ_items[] = $v['product_bn'];
                            unset($items[$key]);
                        }
                    }
                }
                if (!empty($items)){
                    sort($items);
                    $params['items'] = json_encode($items);
                }else{
                    $api_status = 'success';
                    $msg = '过滤已同步成功的货号';
                    $this->updateLog($log_id,$msg,$api_status);
                    return $this->msgOutput('succ', $msg);
                }

                // 更新日志参数中已成功同步的货品数据
                if ($succ_items){
                    $new_params = array();
                    $new_params[0] = $method;
                    $params['succ_bn'] = json_encode($succ_items);
                    $new_params[1] = $params;
                    $new_params[2] = $callback;
                    $this->updateLog($log_id,'','',$new_params);
                }
                if (isset($params['succ_bn'])){
                    unset($params['succ_bn']);
                }
            }


        if ($omeapi = kernel::single('rpc_caller')){
            if (method_exists($omeapi, 'call')){
                $result = $omeapi->conn('fsockopen')->set_timeout($time_out)->call($url,$method,$params);
                if ($result === false){
                    $status = 'fail';
                    $msg = $result ? $result : 'time_out';
                }else{
                    $res = json_decode($result,1);
                    $res['rsp'] = 'succ';
                    if (isset($res['rsp']) && $res['rsp'] == 'succ'){
                        $status = 'success';
                        $sync_status = 'success';
                    }else{
                        $status = 'fail';
                        $sync_status = 'fail';
                    }            
                    $msg = isset($res['msg']) ? $res['msg'] : $result;
                    $data = isset($res['data']) ? $res['data'] : '';
                }
                
                //更新商品同步状态
                if (in_array($method,array('store.wms.item.add','store.wms.item.update'))){
                    $items = json_decode($params['items'],true);
                    $sync_sku_data = array();

                    #同步状态值兼容1.2.5以上版本
                    $deploy_xml = base_setup_config::deploy_info();
                    $sync_status = '3';
                    
                    foreach($items as $key=>$v){
                        $sync_sku_data[] = array(
                            'inner_sku' => $v['product_bn'],
                            'outer_sku' => '',
                            'status' => $sync_status
                        );
                    }
                    if ($sync_sku_data){
                        $result = $this->set_sync_status($sync_sku_data,$node_id);
                    }
                }

                //更新日志状态
                $addon['msg_id'] = isset($res['msg_id']) && $res['msg_id'] ? $res['msg_id'] : '';
                $this->updateLog($log_id,$msg,$status,'',$addon);
                return $this->msgOutput($status, $msg, '',$data);
            }
        }else{
            return $this->msgOutput('fail', 'rpc_caller not found!');
        }
    }

    /**
    * 通过货号获取条形码
    * @access public 
    * @param String $bn 货号
    * @return String 条形码
    */
    public function getBarcode($bn=''){
        if($bn == '') return null;
        $bn = trim($bn);

        $products_mdl = app::get('ome')->model('products');
        $product = $products_mdl->getList('barcode',array('bn'=>$bn),0,1);
        $barcode = isset($product[0]) ? $product[0]['barcode'] : $bn;
        return $barcode;
    }

}