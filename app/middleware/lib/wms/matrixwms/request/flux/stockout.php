<?php
/**
* 出库单
*
* @copyright shopex.cn 2015.05.07
* @author sunjing@shopex.cn
*/
class middleware_wms_matrixwms_request_flux_stockout extends middleware_wms_matrixwms_request_stockout{

    /**
     * 出库通知单参数
     * @param   array sdf
     * @return array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockout_create_params( $sdf , $cur_page )
    {
        $params = parent::_getStockout_create_params($sdf , $cur_page);
         $params['warehouse_code'] = $this->getWarehouse_code($sdf['branch_bn']); 
        return $params;
    }

    /**
     * 出库单取消通知参数
     * @param   array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockout_cancel_params( $sdf )
    {
        
        $params = parent::_getStockout_cancel_params($sdf);
        $params['warehouse_code'] = $this->getWarehouse_code($sdf['branch_bn']); 
        return $params;
    }
}