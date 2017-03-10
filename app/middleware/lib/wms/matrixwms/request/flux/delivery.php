<?php
/**
* 发货单
*
* @copyright shopex.cn 2015.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_flux_delivery extends middleware_wms_matrixwms_request_delivery{

    /**
     * 发货参数
     * @param  array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function __getDelivery_create_params($sdf){
        $params = parent::__getDelivery_create_params($sdf);
        
        $params['warehouse_code']=$this->getWarehouse_code($sdf['branch_bn']);
        $params['receiver_time'] = date('Y-m-d H:i:s');
        return $params;
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
        $params = parent::_getDelivery_cancel_params($sdf);
        $params['warehouse_code']=$this->getWarehouse_code($sdf['branch_bn']);
        $params['order_type'] = 'CK10-LD';
        
        return $params;
    }
}