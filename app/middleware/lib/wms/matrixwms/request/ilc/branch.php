<?php
/**
* 发货单
*
* @copyright shopex.cn 2014.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_360buy_branch extends middleware_wms_matrixwms_request_branch{

    /**
     * 获取仓库列表
     * @param   
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    public function get_warehouse_list($sdf,$sync)
    {
        
        $writelog = array(
            'log_title' => '获取京东仓库列表',
            'log_type' => 'store.wms.warehouse.list.get',

        );
        $method = 'store.wms.warehouse.list.get';

        return $this->request($method,$params,$writelog,$sync);
    }

    /**
     * 获取仓库列表
     * @param   
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    public function get_logistics_list($sdf,$sync)
    {
        
        $writelog = array(
            'log_title' => '获取京东仓库列表',
            'log_type' => 'store.wms.logistics.companies.get',

        );
        $method = 'store.wms.logistics.companies.get';

        return $this->request($method,$params,$writelog,$sync);
    }
}