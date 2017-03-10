<?php
/**
* 供应商
*
* @copyright shopex.cn 2014.07.30
* @author sunjing@shopex.cn
*/
class middleware_wms_matrixwms_request_supplier extends middleware_wms_matrixwms_request_common{

    /**
     * 
     * @param   
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    public function supplier_create($sdf = array(),$sync=false)
    {
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
        }else{
            return array('rsp'=>'fail','msg'=>'接口方法不存在','error_code'=>'w402');
        }
        
    }

    
    
    protected function __get_params()
    {
        
    }

    public function supplier_add_callback($callback_result,$callback_params){


        return $this->callback($callback_result,$callback_params);
    }
}