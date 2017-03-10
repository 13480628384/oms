<?php
/**
* 接收基类
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_ilcwms_response_common extends middleware_wms_abstract{

    /**
    * 接收
    *
    * @access public
    * @param String $method 业务方法
    * @param String $params 业务参数
    * @return Array 标准输出格式
    */
    public function response($method,&$params){
        
        $wms_class = 'middleware_iostock';
        $wms_method = $method;
        $wmsObj = kernel::single($wms_class);
        if(!method_exists($wmsObj,$wms_method)){
            return $this->msgOutput($rsp='fail',$msg='wms_method '.$wms_method.' NOT FOUND');
        }

        #参数格式化
        self::_params_format($params);
        
        $rs = $wmsObj->$wms_method($params);
        $rsp = isset($rs['rsp']) ? $rs['rsp'] : 'fail';
        $msg = isset($rs['msg']) ? $rs['msg'] : '';
        $msg_code = isset($rs['msg_code']) ? $rs['msg_code'] : '';
        $data = isset($rs['data']) ? $rs['data'] : array();

        return $this->msgOutput($rsp,$msg,$msg_code,$data);
    }

    private static function _params_format(&$params){
        if (isset($params['items'])){
            foreach ($params['items'] as $key=>$item){
                if (isset($item['bn'])) {
                    $params['items'][$key]['bn'] = self::getBnByBarcode($item['bn']);
                }
                
                if (isset($item['product_bn'])) {
                    $params['items'][$key]['product_bn'] = self::getBnByBarcode($item['product_bn']);
                }
            }
        }

    }
    
    /**
    * 通过条形码获取货号
    * @access public 
    * @param String $barcode 条形码
    * @return String 货号
    */
    public static function getBnByBarcode($barcode=''){
        if($barcode == '') return null;
        $barcode = trim($barcode);

        $products_mdl = app::get('ome')->model('products');
        $product = $products_mdl->getList('bn',array('barcode'=>$barcode),0,1);
        $bn = isset($product[0]) ? $product[0]['bn'] : $barcode;

        return $bn;
    }


}