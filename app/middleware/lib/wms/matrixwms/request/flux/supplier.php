<?php
/**
* 供应商
*
* @copyright shopex.cn 2014.07.30
* @author sunjing@shopex.cn
*/
class middleware_wms_matrixwms_request_flux_supplier extends middleware_wms_matrixwms_request_supplier{

     
     
     
     

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
            'CustomerID'=>$sdf['bn'],//
            'vendor_ename'=>$sdf['name'],//
            'vendor_name'=>$sdf['name'],
            'address'=>$sdf['addr'],//
            'state'=>$state,//
            'city'=>$city,//
            'country'=>'中国',//
          
        );
        return $data;
    }

    
}