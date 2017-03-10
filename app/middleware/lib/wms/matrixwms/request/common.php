<?php
/**
* 发起基类
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_matrixwms_request_common extends middleware_wms_abstract{

    protected $limit = 150;
    public $inventory_type = array(
        '5'=>'101',//残次品
        '50'=>'101',
        '300'=>'401',//样品
        '400'=>'501',//新品
    );
    /**
    * 设置业务调用方的异步回调
    *
    */
    public function setUserCallback($callback_class,$callback_method,$callback_params=null){
        $this->callback_class = $callback_class;
        $this->callback_method = $callback_method;
        $this->callback_params = $callback_params;
    }

    /**
    * 设置节点号
    *
    */
    public function setNodeId($node_id=''){
        $this->node_id = $node_id;
        $this->node_name = kernel::single('middleware_adapter')->getChannelNameByNodeId($node_id);
    }

    /**
    * 调用
    * @param String $method 接口方法
    * @param Array $params 接口参数
    * @param Array $writelog 日志信息
    * @param bool $sync 同异步类型:false(异步),true(同步),默认false
    * @param Array $adapter_callback 适配器callback
     * @param Int $time_out 超时时间
    * @return Array 标准输出格式
    */
    public function request($method,$params,$writelog,$sync=false,$adapter_callback=array(),$time_out=120){
        
        #日志信息
        $log_title = $writelog['log_title'].'('.$this->node_name.')';
        $original_bn = $writelog['original_bn'];
        $log_type = $writelog['log_type'];
        $log_id = isset($writelog['log_id']) ? $writelog['log_id'] : '';
        $addon['original_params'] = $writelog['original_params'];
        $addon['addon'] = $writelog['addon'];
        $addon['sync'] = $sync == true ? 'true' : 'false';
        
        #目标节点号
        $params['to_node_id'] = $this->node_id;
        $log_id = $log_id ? $log_id : $this->getLogId();

        //设置callback异常返回参数为空时的默认值
        $rpc_callback = array();
        $callback_class = isset($adapter_callback['class']) && $adapter_callback['class'] ? $adapter_callback['class'] : __CLASS__;
        $callback_method = isset($adapter_callback['method']) && $adapter_callback['method'] ? $adapter_callback['method'] : 'callback';
        $rpc_callback = array($callback_class,$callback_method,array_merge(array('log_id'=>$log_id,'node_id'=>$this->node_id,'userCallback_class'=>$this->callback_class,'userCallback_method'=>$this->callback_method,'userCallback_params'=>$this->callback_params),array('callback_params'=>$adapter_callback['params'])));
        //添加日志
        if(!isset($writelog['log_id']) || empty($writelog['log_id'])){
            $retry_class = __CLASS__;
            $retry_method = 'rpc_request';
            $retry_params = array($method, $params, $rpc_callback);
            $this->writeLog($log_id,$log_title,$retry_class,$retry_method,$retry_params,$memo='',$api_type='request',$status='fail',$msg='请求中',$addon,$log_type,$original_bn);
        }

        return $this->rpc_request($method,$params,$rpc_callback,$sync,$time_out);
    }

    /**
     * RPC开始请求
     * 业务层数据过滤后，开始向上级框架层发起
     * @access public
     * @param String $method RPC远程服务接口名称
     * @param array $params 业务参数
     * @param array $callback 异步返回
     * @param boolean $sync 异步方式:false,同步:true
     * @param int $time_out 发起超时时间（秒）
     * @return RPC响应结果
     */
    public function rpc_request($method,$params,$callback,$sync=false,$time_out=120){

        $mode = $sync == true ? 'sync' : 'async';
        $callback_class = $callback[0];
        $callback_method = $callback[1];
        $callback_params = (isset($callback[2])&&$callback[2])?$callback[2]:array();
        $log_id = $callback_params['log_id'];
        
        if ($sync == false){// 异步
            //商品日志重试,过滤已同步成功的货号
            if (in_array($method,array('store.wms.item.add','store.wms.item.update'))){
                $node_id = $params['to_node_id'];
                if ($params['item_lists']){
                    $succ_items = array();
                    $lists = json_decode($params['item_lists'],true);
                    foreach($lists['item'] as $key=>$items){
                        if ($this->issync($items['item_code'],$node_id)){
                            $succ_items[] = $items['item_code'];
                            unset($lists['item'][$key]);
                        }
                    }
                }
                if (!empty($lists['item'])){
                    $items = $lists['item'];
                    sort($items);
                    $params['item_lists'] = json_encode(array('item'=>$items));
                }else{
                    return $this->updateLog($log_id,'货品已同步成功','success');
                }

                // 更新api参数
                if ($succ_items){
                    $new_params = array();
                    $new_params[0] = $method;
                    $params['succ_bn'] = json_encode($succ_items);
                    $new_params[1] = $params;
                    $new_params[2] = $callback;
                    $this->updateLog($log_id,$msg=NULL,$status=NULL,$new_params);
                }
                if (isset($params['succ_bn'])){
                    unset($params['succ_bn']);
                }
            }
        }
        $headers = array();
        $gzip = false;
        if (isset($params['gzip']) && $params['gzip'] == 'true'){
            $headers['Content-Encoding'] = 'gzip';
            $gzip = true;
        }
        $time_out = !empty($time_out) ? $time_out : 5;
        if (isset($params[1]['task'])){
            $rpc_id = $params[1]['task'];
        }

        #更新任务日志参数
        if ($sync == false){
            $new_params = array();
            if (!in_array($method,array('store.wms.item.add','store.wms.item.update'))){
                $new_params[0] = $method;
                $new_params[1] = $params;
                $new_params[2] = $callback;
                
                $this->updateLog($log_id,$msg=NULL,$status=NULL,$new_params);
            }
        }

        $re =  kernel::single('rpc_caller')->conn('matrix')
                      ->set_callback($callback_class,$callback_method,$callback_params)
                      ->set_timeout($time_out)->set_version('1.1')->set_app('ome')
                      ->call($url='',$method,$params,$mode,$headers);

        if ($re['rsp'] != 'succ'){
            $convent_re = $this->wms_error($re['err_code']);
        }
        if ($convent_re){
            $re = array_merge($this->msgOutput($convent_re['rsp'], $convent_re['msg'], $re['err_msg'],$re['data']), array('msg_id'=>$re['msg_id'],'error_lv'=>$convent_re['error_lv'],'err_msg'=>$re['err_msg'],'msg_code'=>$re['err_code']));
        }else{
            $re['msg_code'] = $re['err_code'];
        }

        //更新商品同步状态
        if ($re['rsp'] == 'fail' && in_array($method,array('store.wms.item.add','store.wms.item.update'))){
            $sync_sku_data = array();
            $lists = json_decode($params['item_lists'],true);
            foreach($lists['item'] as $key=>$items){
                $sync_sku_data[] = array(
                    'inner_sku' => $items['item_code'],
                    'outer_sku' => '',
                    'status' => '1'
                );
            }
            if ($sync_sku_data){
                $this->set_sync_status($sync_sku_data,$node_id);
            }
        }
        
        //更新日志状态
        $addon = array();
        if (isset($re['error_lv']) && $re['error_lv']){
            $addon['error_lv'] = $re['error_lv'];
        }
        if (isset($re['msg_id']) && $re['msg_id']){
            $addon['msg_id'] = $re['msg_id'];
        }
        $this->updateLog($log_id,$re['msg'],$re['rsp'],'',$addon);
        
        $re['msg_code'] = isset($re['msg_code']) ? kernel::single('middleware_wms_matrixwms_errcode')->getWmsErrCode($re['msg_code']) : $re['msg_code'];
        return $re;
    }

    /**
     * RPC同步返回数据接收
     * @access public
     * @param json array $res RPC响应结果
     * @param array $params 同步日志ID
     */
    public function response_log($res, $params){
        $response = json_decode($res, true);
        if (is_array($response)){
            $status = $response['rsp'];
            $msg_id = $response['msg_id'];
        }else{
            $status = 'fail';
        }
        //更新日志msg_id及在应用级参数中记录task
        if ($msg_id){
            $log_id = $params['log_id'];
            $log_info = $this->getLogDetail(array('log_id'=>$log_id));
           
            $log_params = unserialize($log_info['params']);
            $rpc_key = $params['rpc_key'];
            $log_params[1]['task'] = $rpc_key;
            $addon = array(
                'msg_id' => $msg_id,
                //'params' => serialize($log_params),
            );

            $this->updateLog($log_id,'',$status,'',$addon);
        }
    }

    /**
     * RPC异步返回数据接收
     * @access public
     * @param object $result 经由框架层处理后的同步结果数据
     * @return 返回业务处理结果
     */
    public function callback($result,$callback_params=NULL){

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
        }else{
            $res = 'log_id is not empty';
        }

        return array('rsp'=>$rsp, 'res'=>$res, 'msg_id'=>$msg_id, 'log_id'=>$log_id);
    }

    /**
     * 批量单据创建异步返回处理
     * @access public
     * @param object $result 经由框架层处理后的同步结果数据
     * @param object $ext_params 第2页以上的发起类与方法
     * @return 返回业务处理结果
     */
    public function batch_callback($result,$callback_params=NULL,$ext_params=array()){

        #调用用户callback
        $this->callUserCallback($callback_params);

        #更新日志状态信息
        $addon = $data = array();
        $msg_code = $result['res'];
        $msg = $result['err_msg'].'('.serialize($result).')';
        $status = $result['rsp'];
        $data = $result['data'];
        $msg_id = $result['msg_id'];

        $log_id = $callback_params['log_id'];
        $log_detail = $this->getLogDetail(array('log_id'=>$log_id), 'status,msg_id,params,original_bn,addon');

        // 成功不处理
        if ($log_detail['status'] != 'success'){
            $params = unserialize($log_detail['params']);
            $original_params = unserialize($log_detail['original_params']);
            $msg_ids = unserialize($log_detail['addon']);
            $cur_page = $params[1]['current_page'];
            $total_page = $params[1]['total_page'];
            $node_id = $params[1]['to_node_id'];
            $new_params = array();

            // 防并发
            if ($msg_ids['msg_id']){
                if (in_array($msg_id, $msg_ids['msg_id'])) return;
            }

            $api_send = true;
            // 返回成功:发起下一页,并更新当前同步页码为当前页+1
            if($status == 'succ'){
                if ($cur_page >= $total_page){
                    $api_status = 'success';
                    $msg .= serialize($data);
                    $api_send = false;
                }else{
                    $api_status = 'running';
                    $msg = '当前单据同步共有'.$total_page.'页,已同步成功'.$cur_page.'页,正在同步第'.($cur_page+1).'页 - '.serialize($data);
                    // 更新当前任务重试次数为0
                    $addon['retry'] = 0;
                }
                $new_msg_id = $msg_ids['msg_id'] ? $msg_ids['msg_id'] : array();
                array_push($new_msg_id,$msg_id);
                $addon['addon']['msg_id'] = $new_msg_id;
                //更新外部单号
                if ($data['wms_order_code']) {
                    
                }
            }else{
                // 失败不发起任务
                $api_status = 'fail';
                $api_send = false;
            }

            //错误编码
            $addon['msg_code'] = $msg_code;

            $this->updateLog($log_id,$msg,$api_status,'',$addon);
            $rsp = 'succ';

//            if ($api_send){
//                $call_class = $ext_params['class'];
//                $call_method = $ext_params['method'];
//                $_instance = kernel::single($call_class);
//                $_instance->setUserCallback($params[2][2]['userCallback_class'],$params[2][2]['userCallback_method'],$params[2][2]['userCallback_params']);
//                $_instance->setNodeId($node_id);
//                $_instance->$call_method($original_params,$sync=false,++$cur_page,$log_id);
//            }
        }else{
            $rsp = 'succ';
        }
        
        return array('rsp'=>$rsp, 'res'=>$res, 'msg_id'=>$msg_id, 'log_id'=>$log_id);
    }

    protected static function getItems($items,$page='1',$page_size='30'){
        if(empty($items)) return;

        $offset = ($page-1)*$page_size;
        $new_items = array();
        sort($items);
        for($key=$offset;$key<$offset+$page_size;$key++){
            if(!isset($items[$key])) break;
            $new_items[] = $items[$key];
        }
       
        return $new_items;
    }

    public function callUserCallback($callback_params){
        #调用用户callback
        $user_callback_class = $callback_params['userCallback_class'];
        $user_callback_method = $callback_params['userCallback_method'];
        $user_callback_params = $callback_params['userCallback_params'];
        if($user_callback_class && $user_callback_method){
            if(class_exists($user_callback_class)){
               $_instance = kernel::single($user_callback_class);
               if(method_exists($_instance,$user_callback_method)){
                    $_instance->$user_callback_method($callback_result,$user_callback_params);
               }
            }
        }
    }

    /**
    * wms标准错误信息编码
    * @access public
    * @param String $code 错误代码
    * @return mixed
    */
    public function wms_error($code){
        $code = trim($code);
        if (empty($code)) return NULL;

        $error = array(
            'w03101' => array(
                'msg' => '单据号不存在',
                'rsp' => 'success',
            ),
            'w03102' => array(
                'msg' => '单据已发货',
                'rsp' => 'fail',
            ),
            'w03103' => array(
                'msg' => '单据已取消',
                'rsp' => 'success',
            ),
            'w03104' => array(
                'msg' => '单据已关闭',
                'rsp' => 'success',
            ),
            'w03105' => array(
                'msg' => '单据号重复',
                'rsp' => 'fail',
            ),
            'w03106' => array(
                'msg' => '单据已收货',
                'rsp' => 'fail',
            ),
            'w03107' => array(
                'msg' => '退货单原始订单号不对',
                'rsp' => 'fail',
            ),
            'w03108' => array(
                'msg' => '单据不能撤销',
                'rsp' => 'fail',
            ),
            'w03020' => array(
                'msg' => '第三方仓储无取消业务',
                'rsp' => 'fail',
                'error_lv' => 'warning'
            ),
        );
        if ( isset($error[$code]) ){
            return $error[$code];
        }else{
            return NULL;
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

     
    /**
     * 获取仓库WMS编号.
     * @param  
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    public function getWarehouse_code($branch_bn)
    {
        $branch_relationObj = app::get('wmsmgr')->model('branch_relation');
        $branch_relation = $branch_relationObj->dump(array('sys_branch_bn'=>$branch_bn));
        $wms_branch_bn = $branch_relation['wms_branch_bn'];
        $wms_branch_bn = $wms_branch_bn ? $wms_branch_bn : $branch_bn;
        return $wms_branch_bn;
    }
}