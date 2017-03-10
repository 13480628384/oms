<?php
/**
* 退货入库单
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_ilcwms_request_reship extends middleware_wms_ilcwms_request_common{

    /**
    * 退货单创建
    * @access public
    * @param Array $sdf 退货入库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function reship_create(&$sdf,$sync=false){

       //状态判断,出库单状态为取消，则不发起同步
       if($this->iscancel($sdf['reship_bn'],'reship')){
            return $this->msgOutput('success','退货单已取消,终止同步');
       }

       // 退货明细
        foreach ($sdf['items'] as $v){
            $barcode = $this->getBarcode($v['bn']);#TODO:伊腾忠用条形码作唯一标识
            $items[] = array(
                'item_bn' => $barcode,
                'num' => $v['num'],
                'price' => $v['price'],
            );
        }

        $params = array(
            'order_bn' => $sdf['reship_bn'],
            'warehouse' => $sdf['branch_bn'],
            'member_name' => $sdf['member']['name'],
            'logistics' => $sdf['logi_name'],
            'logi_no' => $sdf['logi_no'],
            'logi_money' => $sdf['money'],
            'memo' => $sdf['memo'],
            'original_delivery_bn' => $sdf['original_delivery_bn'],// 原始发货单号
            'reshipper_name' => $sdf['receiver_name'],// 退货人姓名
            'reshipper_zip' => $sdf['receiver_zip'],// 退货人邮编
            'reshipper_telephone' => $sdf['receiver_phone'],// 退货人固定电话
            'reshipper_mobile' => $sdf['receiver_mobile'],// 退货人手机
            'reshipper_email' => $sdf['receiver_email'],// 退货人邮箱
            'reshipper_province' => $sdf['receiver_state'],// 退货人所在省(字符串，如上海市
            'reshipper_city' => $sdf['receiver_city'],// 退货人所在市
            'reshipper_district' => $sdf['receiver_district'] ? $sdf['receiver_district'] : '其它',// 退货人所在县区
            'reshipper_addr' => $sdf['receiver_address'],// 退货人详细地址     
            'items' => json_encode($items),
        );

        $writelog = array(
            'log_title' => '退货单添加',
            'log_type' => 'store.trade.reship',
            'original_bn' => $sdf['reship_bn'],
        );
        $method = 'store.wms.returnorder.create';

        return $this->request($method,$params,$writelog,$sync);
    }

    /**
    * 退货单创建取消
    * @access public
    * @param Array $sdf 退货入库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function reship_cancel(&$sdf,$sync=false){
        return array('rsp'=>'fail','msg'=>'接口方法不存在','msg_code'=>'w402');
    }

}