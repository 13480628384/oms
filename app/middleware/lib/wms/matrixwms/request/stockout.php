<?php
/**
* 出库单
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_matrixwms_request_stockout extends middleware_wms_matrixwms_request_common{

    public $stockout_type = array(
        'PURCHASE_RETURN' => 'OUT_PURCHASE_RETURN',//采购退货
        'ALLCOATE' => 'OUT_ALLCOATE',//调拨出库
        'DEFECTIVE' => 'OUT_DEFECTIVE',// 残损出库
        'ADJUSTMENT' => 'OUT_ADJUSTMENT',// 调帐出库
    );

    /**
    * 出库通知
    * @access public
    * @param Array $sdf 出库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockout_create(&$sdf,$sync=false,$cur_page='1',$log_id=''){

        $stockout_bn = $sdf['io_bn'];

        // 状态判断,出库单状态为取消，则不发起同步
        if ( $this->iscancel($stockout_bn,'stockout') ){
            return $this->msgOutput('succ','出库单已取消,终止同步');
        }

        $items_count = count($sdf['items']);
        $total_page = ceil($items_count/$this->limit);
        $sdf['batch_id'] = substr(self::uniqid(),0,25);
        for ($page=1;$page<=$total_page;$page++)
        {
            $params = $this->_getStockout_create_params($sdf,$page);

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

    /**
    * 出库通知callback接收
    * @access public
    * @param Array $callback_result 出库单callback结果(标准的msgOutput:middleware_message)
    * @param Array $callback_params adapter_callback参数
    * @return Array 标准输出格式
    */
    public function stockout_create_callback($callback_result,$callback_params){
        $ext_params = array('class'=>__CLASS__,'method'=>'stockout_create');
        return $this->batch_callback($callback_result,$callback_params,$ext_params);
    }

    
    /**
     * 出库通知单参数
     * @param   array sdf
     * @return array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockout_create_params( $sdf , $page )
    {
        $stockout_bn = $sdf['io_bn'];
        $items_count = count($sdf['items']);
        $total_page = ceil($items_count/$this->limit);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;
        $sdf_items = self::getItems($sdf['items'],$page,$this->limit);

        if ($sdf_items){
            $items = array();
            $offset = ($page-1) * $this->limit+1;
            foreach ($sdf_items as $v){
                $items[] = array(
                    'item_code' => $v['bn'],
                    'item_name' => $v['name'],
                    'item_quantity' => $v['num'],
                    'item_price' => $v['price'] ? $v['price'] : '0',// TODO: 商品价格
                    'item_line_num' => $offset,// TODO: 订单商品列表中商品的行项目编号，即第n行或第n个商品
                    'trade_code' => '',//可选(若是淘宝交易订单，并且不是赠品，必须要传订单来源编号) 
                    'item_id' => $v['bn'],// 商品ID
                    'is_gift' => '0',// TODO: 判断是否为赠品0:不是1:是
                    'item_remark' => '',// TODO: 商品备注
                    'inventory_type' => '1',// TODO: 库存类型1可销售库存101类型用来定义残次品201冻结类型库存301在途库存
                );
                $offset++;
            }
        }
        $wms_order_code = $stockout_bn;
        $order_type = $this->stockout_type[$sdf['io_type']] ? $this->stockout_type[$sdf['io_type']] : 'OUT_OTHER';
        $create_time = preg_match('/-|\//',$sdf['create_time']) ? $sdf['create_time'] : date("Y-m-d H:i:s",$sdf['create_time']);

        $params = array(
            'uniqid' => self::uniqid(),
            'customer_id' => '',// 客户编码
            'out_order_code' => $stockout_bn,
            'order_type' => $order_type,
            'created' => $create_time,
            'wms_order_code' => $wms_order_code,
            'is_finished' => $page >= $total_page ? 'true' : 'false',
            'current_page' => $page,// 当前批次,用于分批同步
            'total_page' => $total_page,// 总批次,用于分批同步
            'shipping_type' => 'EXPRESS',// TODO: 运输方式 EXPRESS-快递 EMS-邮政速递
            'logistics_code' => '',// TODO: 快递公司（如果是汇购传递快递公司，则该项目不能为空，否则可以为空处理）
            'remark' => $sdf['memo'],
            'total_amount' => $sdf['total_goods_fee'],// 订单商品总价（精确到小数点后2位）
            'receiver_name' => $sdf['receiver_name']  ? $sdf['receiver_name'] : '未知',
            'receiver_zip' => $sdf['receiver_zip'] ? $sdf['receiver_zip'] : '123211',// TODO: 收货人邮政编码
            'receiver_state' => $sdf['receiver_state'] ? $sdf['receiver_state'] : '未知',// TODO: 退货人所在省
            'receiver_city' => $sdf['receiver_city'] ? $sdf['receiver_city'] : '未知',// TODO: 退货人所在市
            'receiver_district' => $sdf['receiver_district'] ? $sdf['receiver_district'] : '未知',// TODO: 退货人所在县（区），注意有些市下面是没有区的
            'receiver_address' => $sdf['receiver_address'] ? $sdf['receiver_address'] : '未知',// TODO: 收货地址（出库时非空）
            'receiver_phone' => $sdf['receiver_phone'] ? $sdf['receiver_phone'] : '未知',// TODO: 收货人电话号码（如有分机号用“-”分隔）(电话和手机必选一项) 
            'receiver_mobile' => $sdf['receiver_mobile'] ? $sdf['receiver_mobile'] : '未知',// TODO: 收货人手机号码(电话和手机必选一项) 
            'receiver_email ' => $sdf['receiver_email'] ? $sdf['receiver_email'] : '未知',// TODO: 收货人手机号码(电话和手机必选一项) 
            'receiver_time' => '',
            'sign_standard' => '',// TODO: 签收标准（如：身仹证150428197502205130）
            'source_plan' => '',// TODO: 来源计划点
            'line_total_count' => $sdf['line_total_count'],// TODO: 订单行项目数量
            'item_total_num' => $sdf['item_total_num'],
            'storage_code' => $sdf['storage_code'],// 库内存放点编号
            'items' => json_encode(array('item'=>$items)),
        );
        return $params;
    }
    /**
    * 出库单取消
    * @access public
    * @param Array $sdf 出库单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockout_cancel(&$sdf,$sync=false){

        $stockout_bn = $sdf['io_bn'];
        $order_type = $this->stockout_type[$sdf['io_type']] ? $this->stockout_type[$sdf['io_type']] : 'OUT_OTHER';

        $params = $this->_getStockout_cancel_params( $sdf );
        
        $writelog = array(
            'log_title' => '出库单取消',
            'log_type' => 'store.trade.stockout',
            'original_bn' => $stockout_bn,
        );
        $method = 'store.wms.outorder.cancel';

        return $this->request($method,$params,$writelog,$sync);
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
        );

        return $params;
    }

    
    /**
     * 出库查询
     * @param   
     * @return  
     * @access  public
     * @author 
     */
    public function stockout_search($sdf=array(),$sync=false)
    {
        $stockout_bn = $sdf['stockout_bn'];
        $params = $this->_getStockout_search_params($sdf);
        $writelog = array(
            'log_title' => '出库单查询',
            'log_type' => 'store.trade.stockout',
            'original_bn' => $stockout_bn,
        );
        $method = 'store.wms.outorder.get';

        return $this->request($method,$params,$writelog,$sync);
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