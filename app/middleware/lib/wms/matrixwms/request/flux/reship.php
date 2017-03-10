<?php
/**
* 退货单
*
* @copyright shopex.cn 2015.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_flux_reship extends middleware_wms_matrixwms_request_reship{

   
    /**
     * 退货单添加退货退知参数
     *@access protected
    * @param array
    */

    protected function _getReship_create_params($sdf)
    {
        $params = parent::_getReship_create_params($sdf);
        $params['warehouse_code'] = $this->getWarehouse_code($sdf['branch_bn']); 
        return $params;
    }
}