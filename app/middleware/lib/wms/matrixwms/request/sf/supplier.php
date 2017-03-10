<?php
/**
* 供应商
*
* @copyright shopex.cn 2014.07.30
* @author sunjing@shopex.cn
*/
class middleware_wms_matrixwms_request_sf_supplier extends middleware_wms_matrixwms_request_supplier{

     /**
    * 供应商添加
    * @access public
    * @param Array $sdf 商品数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function supplier_create(&$sdf,$sync=false){

        $params = $this->__get_params($sdf);
        
        if ($params) {
            $adapter_callback = array(
                'class' => __CLASS__,
                'method' => 'supplier_add_callback',
            );
            $writelog = array(
                'log_title' => '供应商添加',
                'log_type' => 'store.vendors.get',
                'addon' => '',
            );
            $method = 'store.vendors.get';

            $this->request($method,$params,$writelog,$sync,$adapter_callback);
            return array('rsp'=>'succ','msg'=>'同步成功');
        }
        
    }

     
     
     /**
      * 
      * @param   array
      * @return  type    
      * @access  public
      * @author sunjing@shopex.cn
      */
     function supplier_add_callback($callback_result,$callback_params)
     {
         $this->callUserCallback($callback_params);
         
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
            if($status == 'succ'){
                $api_status = 'success';
                
                
            }else{
                // 失败不发起任务
                $api_status = 'fail';
                $api_send = false;
            }

            //错误编码
            $addon['msg_code'] = $msg_code;
            $this->updateLog($log_id,$msg,$api_status,'',$addon);
            $rsp = 'succ';
        }else{
            $rsp = 'succ';
        }
         return array('rsp'=>$rsp, 'res'=>$res, 'msg_id'=>$msg_id, 'log_id'=>$log_id);
     }

    /**
    * 供应商添加
    * @access public
    * @param Array $sdf 供应商数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    protected function __get_params(&$sdf,$sync=false){
        $area = $sdf['area'];
        if ($area) {
            $area = explode(':',$area);
            $area_detail = explode('/',$area[1]);
            $state = $area_detail[1];
            $city = $area_detail[0];
        }
        $data = array(
            'vendor'=>$sdf['bn'],//
            'vendor_name'=>$sdf['name'],//
            'address'=>$sdf['addr'],//
            'state'=>$state,//
            'city'=>$city,//
            'country'=>'中国',//
            'interface_action_code'=>'NEW',
        );
        return $data;
    }

    
}