<?php
/**
* 出库单
*
* @copyright shopex.cn 2015.05.07
* @author sunjing@shopex.cn
*/
class middleware_wms_matrixwms_request_fineex_stockout extends middleware_wms_matrixwms_request_stockout{

    public function stockout_create(&$sdf,$sync=false,$cur_page='1',$log_id=''){

        $stockout_bn = $sdf['io_bn'];

        // 状态判断,出库单状态为取消，则不发起同步
        if ( $this->iscancel($stockout_bn,'stockout') ){
            return $this->msgOutput('succ','出库单已取消,终止同步');
        }

        $sdf['batch_id'] = substr(self::uniqid(),0,25);
        
        $this->limit =  count($sdf['items']);
        $params = $this->_getStockout_create_params($sdf,1);

        $adapter_callback = array(
            'class' => __CLASS__,
            'method' => 'stockout_create_callback',
        );
        $writelog = array(
            'log_id' => $log_id,
            'log_title' => '出库单添加',
            'log_type' => 'store.trade.stockout',
            'original_bn' => $stockout_bn,
            'original_params' => serialize($sdf),
        );
        $method = 'store.wms.outorder.create';

        $this->request($method,$params,$writelog,$sync,$adapter_callback);
    }

}