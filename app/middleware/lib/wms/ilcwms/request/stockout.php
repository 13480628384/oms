<?php
/**
* 出库单
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_ilcwms_request_stockout extends middleware_wms_ilcwms_request_common{

    public $stockout_type = array(
        'PURCHASE_RETURN' => 'H',// 采购退货
        'ALLCOATE' => 'R',// 调拨出库
        'DEFECTIVE' => 'B',// 残损出库
    );

    /**
    * 出库单创建
    * @access public
    * @param Array $sdf 出库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockout_create(&$sdf,$sync=false){

        //状态判断,出库单状态为取消，则不发起同步
        if($this->iscancel($sdf['io_bn'],'stockout')){
            return $this->msgOutput('succ','出库单已取消,终止同步');
        }

        $items = array();
        foreach ($sdf['items'] as $v){
            $barcode = $this->getBarcode($v['bn']);#TODO:伊腾忠用条形码作唯一标识
            $items[] = array(
                'item_bn' => $barcode,
                'price' => $v['price'],
                'num' => $v['num'],
            );
        }
        
        $params = array(
            'order_bn' => $sdf['io_bn'],
            'warehouse' => $sdf['branch_bn'],
            'ship_name' => $sdf['receiver_name'],
            'province' => $sdf['receiver_state'],
            'city' => $sdf['receiver_city'],
            'district' => $sdf['receiver_district'],
            'zip' => $sdf['receiver_zip'],
            'addr' => $sdf['receiver_address'],
            'phone' => $sdf['receiver_phone'],
            'type' => $this->stockout_type[$sdf['io_type']],
            'items' => json_encode($items),
        );

        $writelog = array(
            'log_title' => '出库单添加',
            'log_type' => 'store.trade.stockout',
            'action_type' => 'OUTSTOCK.WMS',
            'original_bn' => $sdf['io_bn'],
        );
        $method = 'store.wms.outorder.create';

        return $this->request($method,$params,$writelog,$sync); 
    }

    /**
    * 出库单取消
    * @access public
    * @param Array $sdf 出库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockout_cancel(&$sdf,$sync=false){
        return array('rsp'=>'fail','msg'=>'接口方法不存在','msg_code'=>'w402');
    }
    
}