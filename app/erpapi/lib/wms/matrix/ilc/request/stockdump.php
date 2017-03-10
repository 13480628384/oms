<?php
/**
 * 转储单推送
 *
 * @category 
 * @package 
 * @author chenping<chenping@shopex.cn>
 * @version $Id: Z
 */
class erpapi_wms_matrix_ilc_request_stockdump extends erpapi_wms_request_stockdump
{
    public function stockdump_cancel($sdf){
        return $this->succ('接口不存在');
    }
}