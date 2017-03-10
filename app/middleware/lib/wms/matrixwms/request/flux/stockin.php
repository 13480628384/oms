<?php
/**
* 入库单
*
* @copyright shopex.cn 2015.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_flux_stockin extends middleware_wms_matrixwms_request_stockin{
    
     /**
     * 入库创建通知单参数
     * @param   array sdf
     * @return  sdf
     * @access  protected
     * @author cyyr24@sina.cn
     */
    protected  function _getStockin_create_params( $sdf ,$cur_page)
    {
        $params = parent::_getStockin_create_params($sdf ,$cur_page);
        $params['warehouse_code'] = $this->getWarehouse_code($sdf['branch_bn']); 
        return $params;
    }

    /**
     * 入库单取消参数
     * @param   array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockin_cancel_params($sdf)
    {
        $params = parent::_getStockin_cancel_params($sdf);
        $params['warehouse_code'] = $this->getWarehouse_code($sdf['branch_bn']); 
        
        return $params;
    }
}