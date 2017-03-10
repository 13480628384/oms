<?php
/**
* 入库单
*
* @copyright shopex.cn 2015.05.07
* @author sunjing<sunjing@shopex.cn>
*/
class middleware_wms_matrixwms_request_fineex_stockin extends middleware_wms_matrixwms_request_stockin{

    public function stockin_create(&$sdf,$sync=false,$cur_page='1',$log_id=''){

        $stockin_bn = $sdf['io_bn'];

        // 状态判断,入库单状态为取消，则不发起同步
        if ( $this->iscancel($stockin_bn,'stockin') ){
            return $this->msgOutput('succ','入库单已取消,终止同步');
        }
        $stockin_bn = $sdf['io_bn'];
        $sdf['batch_id'] = substr(self::uniqid(),0,25);
        
        $this->limit =  count($sdf['items']);
        $params = $this->_getStockin_create_params($sdf,1);
        
        $adapter_callback = array(
            'class' => __CLASS__,
            'method' => 'stockin_create_callback',
        );
        $writelog = array(
            'log_id' => $log_id,
            'log_title' => '入库单添加',
            'log_type' => 'store.trade.stockin',
            'original_bn' => $stockin_bn,
            'original_params' => serialize($sdf),
        );
        $method = 'store.wms.inorder.create';

        $this->request($method,$params,$writelog,$sync,$adapter_callback);
    }

}