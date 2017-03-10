<?php
/**
 * 出入库、销售记录
 * @package ome_iostocksales
 * @copyright www.shopex.cn 2011.3.15
 * @author ome
 */
class ome_iostocksales {

    /**
     * 存储出入库、销售记录
     * @access public
     * @param String $data 出入库、销售记录
     * @param String $msg 消息
     * @return boolean 成功or失败
     */
    public function set($data,$io,&$msg,$type=null)
    {
        #拆单配置 ExBOY
        $orderSplitLib    = kernel::single('ome_order_split');
        $split_seting     = $orderSplitLib->get_delivery_seting();
        $allow_commit = false;
        // kernel::database()->exec('begin');
        $iostock_instance = kernel::single('ome_iostock');
        $sales_instance = kernel::single('ome_sales');
        if ( method_exists($iostock_instance, 'set') ){
            //存储出入库记录
            $iostock_data = $data['iostock'];
            if(!$type){
                eval('$type='.get_class($iostock_instance).'::LIBRARY_SOLD;');
            }

            $iostock_bn = $iostock_instance->get_iostock_bn($type);

            if ( $iostock_instance->set($iostock_bn, $iostock_data, $type, $iostock_msg, $io) ){

                if ( method_exists($sales_instance, 'set') )
                {
                    if ($data['sales']['sales_items'])
                    {
                        /*------------------------------------------------------ */
                        //-- [拆单]过滤部分拆分OR部分发货时,不存储销售记录  ExBOY
                        /*------------------------------------------------------ */
                        $get_order_id       = intval($data['sales']['order_id']);
                        $get_delivery_id    = intval($data['sales']['delivery_id']);
                        
                        if(!empty($split_seting))
                        {
                            if($data['split_type'] && $get_order_id)
                            {
                                $allow_commit   = $orderSplitLib->check_order_all_delivery($get_order_id, $get_delivery_id);
                            }
                            
                            #[拆单]获取订单对应所有iostock出入库单
                            $order_delivery_iostock_data    = $orderSplitLib->get_delivery_iostock_data($iostock_data);
                            
                            #多个发货单累加物流成本
                            $delivery_cost_actual           = $orderSplitLib->count_delivery_cost_actual($get_order_id);
                            if($delivery_cost_actual)
                            {
                                $sales_data['delivery_cost_actual']  = $delivery_cost_actual;
                            }
                        }
                        else
                        {
                            #防止_拆单多个发货单后_未发货就关闭“拆单功能”_出现生成多个发货单的错误
                            $allow_commit       = $orderSplitLib->check_order_all_delivery($get_order_id, $get_delivery_id);
                        }
                        
                        if(!$allow_commit)
                        {
                            //存储销售记录
                            $branch_id = '';
                            if ($data['sales']['sales_items']){
                                foreach ($data['sales']['sales_items'] as $k=>$v)
                                {
                                    #[拆单]多个发货单时_iostock_id为NULL重新获取 ExBOY
                                    if(!empty($iostock_data[$v['item_detail_id']]['iostock_id']))
                                    {
                                        $v['iostock_id'] = $iostock_data[$v['item_detail_id']]['iostock_id'];
                                    }
                                    else 
                                    {
                                        $v['iostock_id']   = $order_delivery_iostock_data[$v['item_detail_id']]['iostock_id'];
                                    }
                                    $data['sales']['sales_items'][$k] = $v;
                                }
                            }
                            $data['sales']['iostock_bn'] = $iostock_bn;
                            $sales_data = $data['sales'];
                            $sale_bn = $sales_instance->get_salse_bn();
                            $sales_data['sale_bn'] = $sale_bn;
                            if ( $sales_instance->set($sales_data, $sales_msg) ){
                                $allow_commit = true;
                            }
                        }
                    }
                    else
                    {
                        foreach($data['sales'] as $k=>$v)
                        {
                            /*------------------------------------------------------ */
                            //-- [拆单]过滤部分拆分OR部分发货时,不存储销售记录  ExBOY
                            /*------------------------------------------------------ */
                            $get_order_id       = intval($v['order_id']);
                            $get_delivery_id    = intval($v['delivery_id']);
                            
                            if(!empty($split_seting))
                            {
                                if($data['split_type'] && $get_order_id)
                                {
                                    $allow_commit   = $orderSplitLib->check_order_all_delivery($get_order_id, $get_delivery_id);
                                    
                                    if($allow_commit)
                                    {
                                        continue;
                                    }
                                }
                                
                                #获取订单对应所有iostock出入库单
                                $order_delivery_iostock_data    = $orderSplitLib->get_delivery_iostock_data($iostock_data);
                                
                                #多个发货单累加物流成本
                                $delivery_cost_actual           = $orderSplitLib->count_delivery_cost_actual($get_order_id);
                                if($delivery_cost_actual)
                                {
                                    $data['sales'][$k]['delivery_cost_actual']  = $delivery_cost_actual;
                                }
                            }
                            else
                            {
                                #防止_拆单多个发货单后未发货就关闭“拆单功能”_出现生成多个发货单的错误
                                $allow_commit   = $orderSplitLib->check_order_all_delivery($get_order_id, $get_delivery_id);
                                
                                if($allow_commit)
                                {
                                    continue;
                                }
                            }
                            
                            //存储销售记录
                            $branch_id = '';
                            if ($data['sales'][$k]['sales_items']){
                                foreach ($data['sales'][$k]['sales_items'] as $kk=>$vv)
                                {
                                    #[拆单]多个发货单时_iostock_id为NULL重新获取 ExBOY
                                    if(!empty($iostock_data[$vv['item_detail_id']]['iostock_id']))
                                    {
                                        $vv['iostock_id'] = $iostock_data[$vv['item_detail_id']]['iostock_id'];
                                    }
                                    else 
                                    {
                                        $vv['iostock_id']   = $order_delivery_iostock_data[$vv['item_detail_id']]['iostock_id'];
                                    }
                                    
                                    $data['sales'][$k]['sales_items'][$kk] = $vv;
                                }
                            }
                            $data['sales'][$k]['iostock_bn'] = $iostock_bn;
                            $sale_bn = $sales_instance->get_salse_bn();
                            $data['sales'][$k]['sale_bn'] = $sale_bn;
                            if ( $sales_instance->set($data['sales'][$k], $sales_msg) ){
                                $allow_commit = true;
                            }
                        }

                    }

                }

                //更新销售单上的成本单价和成本金额等字段
                kernel::single('tgstockcost_instance_router')->set_sales_iostock_cost($io,$iostock_data);
            }
        }

        if ($allow_commit == true){
            // kernel::database()->commit();
            return true;
        }else{
            // kernel::database()->rollBack();
            $msg['instock'] = $iostock_msg;
            $msg['sales'] = $sales_msg;
            return false;
        }
    }



    /**
     * 组织出库数据
     * @access public
     * @param String $delivery_id 发货单ID
     * @return sdf 出库数据
     */
    public function get_iostock_data($delivery_id){
        $delivery_items_detailObj = app::get('ome')->model('delivery_items_detail');

        //发货单信息
        $sql = 'SELECT `branch_id`,`delivery_bn`,`op_name`,`delivery_time`,`is_cod` FROM `sdb_ome_delivery` WHERE `delivery_id`=\''.$delivery_id.'\'';
        $delivery_detail = $delivery_items_detailObj->db->selectrow($sql);
        $delivery_items_detail = $delivery_items_detailObj->getList('*', array('delivery_id'=>$delivery_id), 0, -1);

        $iostock_data = array();
        if ($delivery_items_detail){
            foreach ($delivery_items_detail as $k=>$v){
                $iostock_data[$v['item_detail_id']] = array(
                    'order_id' => $v['order_id'],
                    'branch_id' => $delivery_detail['branch_id'],
                    'original_bn' => $delivery_detail['delivery_bn'],
                    'original_id' => $delivery_id,
                    'original_item_id' => $v['item_detail_id'],
                    'supplier_id' => '',
                    'bn' => $v['bn'],
                    'iostock_price' => $v['price'],
                    'nums' => $v['number'],
                    'cost_tax' => '',
                    'oper' => $delivery_detail['op_name'],
                    'create_time' => $delivery_detail['delivery_time'],
                    'operator' => $delivery_detail['op_name'],
                    'settle_method' => '',
                    'settle_status' => '0',
                    'settle_operator' => '',
                    'settle_time' => '',
                    'settle_num' => '',
                    'settlement_bn' => '',
                    'settlement_money' => '0',
                    'memo' => '',
                );
            }
        }
        unset($delivery_detail,$delivery_items_detail);
        return $iostock_data;
    }

///////////////////////////////////////////////////////////

    /**
     * 重写 组织销售单数据
     * @access public
     * @param Array $delivery_id 发货单ID
     * @return sales_data 销售单数据
    **/

    public function get_sales_data($delivery_id,$deliverytime = false){
        $order_original_data = array();
        $sales_data = array();

        $deliveryObj = app::get('ome')->model('delivery');
        $orderIds = $deliveryObj->getOrderIdsByDeliveryIds(array($delivery_id));

        $ome_original_dataLib = kernel::single('ome_sales_original_data');
        $ome_sales_dataLib = kernel::single('ome_sales_data');
        foreach ($orderIds as $key => $orderId){
            $order_original_data = $ome_original_dataLib->init($orderId);
            if($order_original_data){
                $sales_data[$orderId] = $ome_sales_dataLib->generate($order_original_data,$delivery_id);
                if(!$sales_data[$orderId]){
                    return false;
                }
            }else{
                return false;
            }
            unset($order_original_data);
        }

        //平摊预估物流运费，主要处理订单合并发货以及多包裹单的运费问题
        $ome_sales_logistics_feeLib = kernel::single('ome_sales_logistics_fee');
        $ome_sales_logistics_feeLib->calculate($orderIds,$sales_data);

        return $sales_data;

    }
}