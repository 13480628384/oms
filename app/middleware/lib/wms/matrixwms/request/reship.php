<?php
/**
* 退货单
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_matrixwms_request_reship extends middleware_wms_matrixwms_request_common{

    /**
    * 退货通知
    * @access public
    * @param Array $sdf 退货单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function reship_create(&$sdf,$sync=false){

        $reship_bn = $sdf['reship_bn'];

        // 状态判断,退货单状态为取消，则不发起同步
        if ( $this->iscancel($reship_bn,'reship') ){
            return $this->msgOutput('success','退货单已取消,终止同步');
        }

        $params = $this->_getReship_create_params($sdf);

        $adapter_callback = array(
            'class' => __CLASS__,
            'method' => 'reship_create_callback',
        );
        $writelog = array(
            'log_title' => '退货单添加',
            'log_type' => 'store.trade.reship',
            'original_bn' => $reship_bn,
            'original_params' => serialize($sdf),
        );
        $method = 'store.wms.returnorder.create';

        return $this->request($method,$params,$writelog,$sync,$adapter_callback);
    }

    /**
     * 退货单添加退货退知参数
     *@access protected
    * @param array
    */

    protected function _getReship_create_params($sdf)
    {
        $reship_bn = $sdf['reship_bn'];
        $items_count = count($sdf['items']);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;

        if ($sdf['items']){
            $offset = 0;
            $items = array();
            foreach ($sdf['items'] as $v){
                // 获取外部商品sku
                $items[] = array(
                    'item_code' => $v['bn'],
                    'item_name' => $v['name'],
                    'item_quantity' => $v['num'],
                    'item_price' => $v['price'] ? $v['price'] : '0',// TODO: 商品价格
                    'item_line_num' => $offset,// TODO: 订单商品列表中商品的行项目编号，即第n行或第n个商品
                    'trade_code' => '',//可选(若是淘宝交易订单，并且不是赠品，必须要传订单来源编号) 
                    'item_id' => $outer_sku,// 商品ID
                    'is_gift' => '0',// TODO: 判断是否为赠品0:不是1:是
                    'item_remark' => '',// TODO: 商品备注
                    'inventory_type' => '1',// TODO: 库存类型1可销售库存101类型用来定义残次品201冻结类型库存301在途库存
                );
                $offset++;
            }
        }

        $params = array(
            'uniqid' => self::uniqid(),
            'wms_supplier' => '',//    TODO: 服务提供商编号
            'out_order_code' => $reship_bn,
            'warehouse_code' => $sdf['branch_bn'],
            'orig_order_code' => $sdf['original_delivery_bn'],
            'created' => $sdf['create_time'],
            'logistics_no' => $sdf['logi_no'],
            'logistics_code' => $sdf['logi_name'],//物流公司
            'remark' => $sdf['memo'],
            'platform_order_code'=>$sdf['order_bn'],//订单号
            'wms_order_code' => $reship_bn,
            'is_finished' => 'true',
            'current_page' => '1',// 当前批次,用于分批同步
            'total_page' => '1',// 总批次,用于分批同步
            'receiver_name' => $sdf['receiver_name'],
            'receiver_zip' => $sdf['receiver_zip'],
            'receiver_state' => $sdf['receiver_province'],
            'receiver_city' => $sdf['receiver_city'],
            'receiver_district' => $sdf['receiver_district'],
            'receiver_address' => $sdf['receiver_addr'],
            'receiver_phone' => $sdf['receiver_tel'],
            'receiver_mobile' => $sdf['receiver_mobile'],
            'receiver_email' => $sdf['receiver_email'],
            'sign_code' => '',// TODO: 节点标识，请求唯一标识 
            'dest_plan' => '',// TODO: 目的计划点
            'line_total_count' => $sdf['line_total_count'],// TODO: 订单行项目数量
            'storage_code' => $sdf['storage_code'],// 库内存放点编号
            'items' => json_encode(array('item'=>$items)),
        );
        return $params;
    }
    /**
    * 退货通知callback接收
    * @access public
    * @param Array $callback_result 退货单callback结果(标准的msgOutput:middleware_message)
    * @param Array $callback_params adapter_callback参数
    * @return Array 标准输出格式
    */
    public function reship_create_callback($callback_result,$callback_params){
        return $this->callback($callback_result,$callback_params);
    }

    /**
    * 退货单取消
    * @access public
    * @param Array $sdf 退货单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function reship_cancel(&$sdf,$sync=false){
        
        $reship_bn = $sdf['reship_bn'];

        $params =$this->_getReship_cancel_params($sdf);

        $writelog = array(
            'log_title' => '退货单取消',
            'log_type' => 'store.trade.reship',
            'original_bn' => $reship_bn,
        );
        $method = 'store.wms.returnorder.cancel';

        return $this->request($method,$params,$writelog,$sync);
    }

     
    /**
     * 退货单取消参数
     * @param  array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getReship_cancel_params($sdf)
    {
        
        $params = array(
            'out_order_code' => $sdf['reship_bn'],
            'warehouse_code' => $sdf['branch_bn'],
        );

        return $params;
    }

    
    /**
     * 退货单搜索
     * @param  
     * @return  
     * @access  public
     * @author sunjing@shopex.cn
     */
    function reship_search(&$sdf,$sync=false)
    {
        $adapter_callback = array(
            'class' => __CLASS__,
            'method' => 'reship_search_callback',
        );
        $writelog = array(
            'log_title' => '退货单查询',
            'log_type' => 'store.trade.reship',
            'original_bn' => $sdf['stockout_bn'],
        );
        $method = 'store.wms.returnorder.get';
        $params = $this->_getReship_search_params($sdf);
        return $this->request($method,$params,$writelog,$sync,$adapter_callback);
    }

    
    /**
     * 退货查询参数
     * @param 
     * @return 
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getReship_search_params($sdf)
    {
        $params = array(
            'out_order_code' =>$sdf['out_order_code'],   
        );
        return $params;
    }

    public function reship_search_callback($callback_result,$callback_params){

    }
}