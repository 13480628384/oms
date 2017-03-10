<?php
/**
* 转储单
*
* @copyright shopex.cn 2013.04.08
* @author dongqiujing<123517746@qq.com>
*/
class middleware_wms_matrixwms_request_stockdump extends middleware_wms_matrixwms_request_common{

    /**
    * 转储通知
    * @access public
    * @param Array $sdf 转储单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockdump_create(&$sdf,$sync=false,$cur_page='1',$log_id=''){

        $stockdump_bn = $sdf['stockdump_bn'];

        // 状态判断,转储单状态为取消，则不发起同步
        if ( $this->iscancel($stockdump_bn,'stockdump') ){
            return $this->msgOutput('success','转储单已取消,终止同步');
        }
        $items_count = count($sdf['items']);
        $total_page = ceil($items_count/$this->limit);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;
        for ($page=1;$page<=$total_page;$page++){
            $params = $this->_getStockdump_create_params($sdf,$page);

            $adapter_callback = array(
                'class' => __CLASS__,
                'method' => 'stockdump_create_callback',
            );
            $writelog = array(
                'log_id' => $log_id,
                'log_title' => '转储单添加',
                'log_type' => 'store.trade.stockdump',
                'original_bn' => $stockdump_bn,
                'original_params' => serialize($sdf),
            );
            $method = 'store.wms.transferorder.create';

            $this->request($method,$params,$writelog,$sync,$adapter_callback);
        }
    }

    /**
    * 转储通知callback接收
    * @access public
    * @param Array $callback_result 转储单callback结果(标准的msgOutput:middleware_message)
    * @param Array $callback_params adapter_callback参数
    * @return Array 标准输出格式
    */
    public function stockdump_create_callback($callback_result,$callback_params){
        $ext_params = array('class'=>__CLASS__,'method'=>'stockdump_create');
        return $this->batch_callback($callback_result,$callback_params,$ext_params);
    }

    
    /**
     * 转储单创建参数
     * @param   array sdf
     * @return  array
     * @access protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockdump_create_params( $sdf,$cur_page )
    {
        $stockdump_bn = $sdf['stockdump_bn'];
        $items_count = count($sdf['items']);
        $total_page = ceil($items_count/$this->limit);
        $sdf['item_total_num'] = $sdf['line_total_count'] = $items_count;
        $sdf_items = self::getItems($sdf['items'],$cur_page,$this->limit);

        if ($sdf_items){
            $items = array();
            $offset = ($cur_page-1) * $this->limit+1;
            foreach ($sdf_items as $v){
                $items[] = array(
                    'item_code' => $v['bn'],
                    'item_name' => $v['name'],
                    'item_quantity' => $v['num'],
                    'item_price' => $v['price'] ? $v['price'] : '0',// TODO: 商品价格
                    'item_line_num' => $offset,// TODO: 订单商品列表中商品的行项目编号，即第n行或第n个商品
                    'item_remark' => '',// TODO: 商品备注
                );
                $offset++;
            }
        }
        
        $wms_order_code = $stockdump_bn;
        $params = array(
            'uniqid' => self::uniqid(),
            'original_id' => $stock_id,
            'out_order_code' => $wms_order_code,
            'created' => $sdf['create_time'],
            'is_finished' => $cur_page >= $total_page ? 'true' : 'false',
            'current_page' => $cur_page,// 当前批次,用于分批同步
            'total_page' => $total_page,// 总批次,用于分批同步
            'remark' => $sdf['memo'],
            'src_storage' => $sdf['src_storage'],//来源存放点编号
            'dest_storage' => $sdf['dest_storage'],//目的存放点编号 
            'line_total_count' => $sdf['line_total_count'],// TODO: 订单行项目数量
            'items' => json_encode(array('item'=>$items)),
        );
        return $params;
    }
    /**
    * 转储单取消
    * @access public
    * @param Array $sdf 转储单数据
    * @param String $sync 同异步类型：false(同步)、true(异步)，默认true
    * @return Array 标准输出格式
    */
    public function stockdump_cancel(&$sdf,$sync=false){

        $stockdump_bn = $sdf['stockdump_bn'];

        $params = $this->_getStockdump_cancel_params( $sdf );

        $writelog = array(
            'log_title' => '转储单取消',
            'log_type' => 'store.trade.stockdump',
            'original_bn' => $stockdump_bn,
        );
        $method = 'store.wms.transferorder.cancel';

        return $this->request($method,$params,$writelog,$sync);
    }

    
    /**
     * 转储单取消参数
     * @param   array sdf
     * @return  array
     * @access  protected
     * @author sunjing@shopex.cn
     */
    protected function _getStockdump_cancel_params( $sdf )
    {
        $params = array(
            'out_order_code' => $sdf['stockdump_bn'],
        );
        return $params;
    }
}