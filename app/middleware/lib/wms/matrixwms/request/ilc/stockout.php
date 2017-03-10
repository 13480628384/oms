<?php
/**
* 出库单
*
* @copyright shopex.cn 2014.05.07
* @author sunjing@shopex.cn
*/
class middleware_wms_matrixwms_request_ilc_stockout extends middleware_wms_matrixwms_request_stockout{
     public $ilc_stockout_type = array(
        'PURCHASE_RETURN' => '采购退货',// 采购退货
        'ALLCOATE' => '调拨出库',// 调拨出库
        'DEFECTIVE' => '残损出库',// 残损出库
    );
    /**
     * 出库通知单参数
     * @param   array sdf
     * @return array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockout_create_params( $sdf , $cur_page )
    {
        $io_type = $sdf['io_type'];
        $order_type = $this->ilc_stockout_type[$io_type] ? $this->ilc_stockout_type[$io_type] : '一般出库';
        $order_type = $sdf['branch_type']=='damaged' ? '残损出库':$order_type;
        $params = parent::_getStockout_create_params($sdf , $cur_page);
        $params['shipping_type'] = 'EMS';
        $params['order_type'] = $order_type;
        $params['receiver_country'] = '中国';
        $items = json_decode($params['items'],true);
        foreach ( $items['item'] as &$item ) {
            $barcode = $this->getBarcode($item['item_code']);
            $item['item_code'] = $barcode;
        }
        $params['items'] = json_encode($items);
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
        
        $stockout_bn = $sdf['io_bn'];
        $order_type = $this->stockout_type[$sdf['io_type']] ? $this->stockout_type[$sdf['io_type']] : 'OUT_OTHER';

        $params = array(
            'out_order_code' => $stockout_bn,
            'order_type' => $order_type,
             'uniqid' =>self::uniqid(),
        );
        return $params;
    }

    /**
     * 出库单查询
     * @param   
     * @return  
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockout_search_params($sdf)
    {
        $params = array(
            'out_order_code' =>$sdf['stockout_bn'],   
        );
        return $params;
    }
}