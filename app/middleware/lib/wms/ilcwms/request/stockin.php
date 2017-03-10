<?php
/**
* 入库单
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_ilcwms_request_stockin extends middleware_wms_ilcwms_request_common{
    
    public $stockin_type = array(
        'PURCHASE' => 'I',// 采购入库
        'ALLCOATE' => 'T',// 调拨入库
        'DEFECTIVE' => 'D',// 残损入库
    );


    /**
    * 入库单创建
    * @access public
    * @param Array $sdf 入库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockin_create(&$sdf,$sync=false){
        //状态判断,入库单状态为取消，则不发起同步
        if($this->iscancel($sdf['io_bn'],'stockin')){
            return $this->msgOutput('succ','入库单已取消,终止同步');
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
            'warehouse' => $sdf['branch_bn'],
            'order_bn' => $sdf['io_bn'],
            'type' => $this->stockin_type[$sdf['io_type']],
            'items' => json_encode($items),
        );

        $writelog = array(
            'log_title' => '入库单添加',
            'log_type' => 'store.trade.stockin',
            'action_type' => 'INSTOCK.WMS',
            'original_bn' => $sdf['io_bn'],
        );
        $method = 'store.wms.inorder.create';

        return $this->request($method,$params,$writelog,$sync);
    }

    /**
    * 入库单取消
    * @access public
    * @param Array $sdf 入库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockin_cancel(&$sdf,$sync=false){
        return array('rsp'=>'fail','msg'=>'接口方法不存在','msg_code'=>'w402');
    }

}