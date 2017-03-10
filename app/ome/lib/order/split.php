<?php
/**
 * 订单拆单处理逻辑Lib类
 * 
 * @access public
 * @author wangbiao<wangbiao@shopex.cn>
 * @version $Id: split.php 2015-12-10 15:00
 */
class ome_order_split
{
    /**
     * [拆单配置]获取拆单后回写发货单方式
     * 注：电子面单线 与 协同版获取拆单配置不一样；
     * 
     * @return array
     */
    public function get_delivery_seting()
    {
        $split_config    = array();
        $split_config['set']['split']          = app::get('ome')->getConf('ome.order.split');
        $split_config['set']['split_model']    = app::get('ome')->getConf('ome.order.split_model');
        $split_config['set']['split_type']     = app::get('ome')->getConf('ome.order.split_type');
        
        $split_seting   = array('split'=>intval($split_config['set']['split']), 'split_model'=>intval($split_config['set']['split_model']),
                                'split_type'=>intval($split_config['set']['split_type']));
        if(empty($split_seting['split']) || empty($split_seting['split_model']) || empty($split_seting['split_type']))
        {
            return '';
        }
        
        return $split_seting;
    }
    
    /**
     * 判断"拆单方式"配置是否变更
     * 
     * @param intval $order_id
     * @return array
     */
    public function get_split_setup_change($order_id)
    {
        $sql    = "SELECT syn_id, sync, split_model, split_type FROM sdb_ome_delivery_sync WHERE order_id = '".intval($order_id)."' AND sync='succ' ORDER BY dateline DESC";
        $row    = kernel::database()->selectrow($sql);
        
        if(empty($row) || $row['split_model'] == 0)
        {
            return '';//上次未开启拆单或无发货记录
        }
        
        #拆单配置
        $split_seting    = $this->get_delivery_seting();
        
        if($row['split_model'] != $split_seting['split_model'] || $row['split_type'] != $split_seting['split_type'])
        {
            $split_seting['old_split_model']    = $row['split_model'];
            $split_seting['old_split_type']     = $row['split_type'];
            
            return $split_seting;
        }
        
        return $split_seting;
    }
    
    /**
     * 根据发货单统计订单商品重量
     * 
     * @param   Intval $order_id
     * @param   Array  $order_items
     * @return  Number
     */
    public function getDeliveryWeight($order_id, $order_items = array(), $delivery_id = 0)
    {
        $orderItemObj  = app::get('ome')->model('order_items');
        $objectsObj    = app::get('ome')->model('order_objects');
        $productObj    = app::get('ome')->model('products');
        
        $pkgGobj    = app::get('omepkg')->model('pkg_goods');
        $pkgPobj    = app::get('omepkg')->model('pkg_product');
        
        $weight        = 0;
        
        if(empty($order_items) && !empty($delivery_id)) 
        {
            $didObj = app::get('ome')->model('delivery_items_detail');
            $dly_itemlist   = $didObj->getList('delivery_id, order_item_id, product_id, number', array('delivery_id'=>$delivery_id, 'order_id'=>$order_id));
            foreach ($dly_itemlist as $key => $val)
            {
                $order_items[$key]  = array('item_id'=>$val['order_item_id'], 'product_id'=>$val['product_id'], 'number'=>$val['number']);
            }
            unset($dly_itemlist);
        }
        elseif(empty($order_items))
        {
            $orderObj    = app::get('ome')->model('orders');
            
            $weight   = $orderObj->getOrderWeight($order_id);
            return $weight;
        }
        
        #[部分拆分]订单计算本次发货商品重量
        $item_list   = $item_ids = array();
        foreach ($order_items as $key => $val) 
        {
            $item_id     = $val['item_id'];
            $product_id  = $val['product_id'];
            
            $item_list[$item_id]    = $val;            
            $item_ids[]             = $item_id;
        }
        
        #获取本次发货单关联的订单明细
        $obj_list = array();
        $flag     = true;
        
        $filter     = array('item_id'=>$item_ids, '`delete`'=>'false');        
        $item_data  = $orderItemObj->getList('item_id, obj_id, product_id, bn, item_type, nums', $filter);
        foreach ($item_data as $key => $val) 
        {
            $item_type   = $val['item_type'];
            $item_id     = $val['item_id'];
            $obj_id      = $val['obj_id'];
            $product_id  = $val['product_id'];
            $bn          = $val['bn'];
            
            $val['send_num']   = $item_list[$item_id]['number'];//发货数量
            
            if($item_type == 'pkg') 
            {
                $obj_list[$obj_id]['items'][$item_id]  = $val;
                
                //[捆绑商品]货号bn
                if(empty($obj_list[$obj_id]['bn'])) 
                {
                    $obj_item     = $objectsObj->getList('obj_id, bn', array('obj_id'=>$obj_id), 0, 1);
                    $obj_list[$obj_id]['bn']  = $obj_item[0]['bn'];
                    
                    //[捆绑商品]重量
                    $pkg_goods    = $pkgGobj->dump(array('pkg_bn'=>$obj_item[0]['bn']),'goods_id, weight');
                    $obj_list[$obj_id]['net_weight']  = floatval($pkg_goods['weight']);
                    
                    //[捆绑商品]发货数量
                    $pkg_product   = $pkgPobj->dump(array('goods_id'=>$pkg_goods['goods_id'], 'product_id'=>$product_id), 'pkgnum');
                    $obj_list[$obj_id]['send_num']    = intval($val['send_num'] / $pkg_product['pkgnum']);
                    
                    $obj_list[$obj_id]['weight']  = 0;
                    if($obj_list[$obj_id]['net_weight'] > 0)
                    {
                        $obj_list[$obj_id]['weight']     = ($obj_list[$obj_id]['net_weight'] * $obj_list[$obj_id]['send_num']);
                    }
                }
            }
            else 
            {
                //普通商品直接计算重量
                $products = $productObj->dump(array('bn'=>$bn),'weight');
                if($products['weight'] > 0)
                {
                  $weight += ($products['weight'] * $val['send_num']);
                }
                else 
                {
                    $weight    = 0;//有一个商品重量为0,就返回
                    $flag      = false;
                    break;
                }
            }
        }
        
        #捆绑商品无重量的重新计算
        if(!empty($obj_list) && $flag)
        {
            foreach ($obj_list as $obj_id => $obj_item) 
            {
                if($obj_item['weight'] > 0) 
                {
                    $weight += $obj_item['weight'];
                }
                else 
                {
                    foreach ($obj_item['items'] as $item_id => $item)
                    {
                        $products = $productObj->dump(array('bn'=>$item['bn']),'weight');
                        if($products['weight'] > 0)
                        {
                            $weight += ($products['weight'] * $item['send_num']);
                        }
                        else 
                        {
                            $weight    = 0;
                            break 2;
                        }
                    }
                }
            }
        }
        
        return $weight;
    }
    
    /**
     * 判断订单是否进行了拆单操作
     * 
     * @param Number $delivery_id 发货单id
     * @return Boolean
     */
    public function check_order_split($delivery_id)
    {
        $deliveryObj    = app::get('ome')->model('delivery');
        
        #获取订单order_id
        $order_ids     = $deliveryObj->getOrderIdByDeliveryId($delivery_id);
        foreach ($order_ids as $key => $val)
        {
            $order_id    = $val;
        }
        
        #获取关联的发货单id
        $temp_ids       = $deliveryObj->getDeliverIdByOrderId($order_id);
        
        #获取订单是否有未生成的发货单的商品
        $sql   = "SELECT item_id FROM sdb_ome_order_items WHERE order_id = '".$order_id."' AND nums != sendnum AND `delete` = 'false'";
        $row   = kernel::database()->selectrow($sql);
        
        if(count($temp_ids) > 1 || !empty($row))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取关联的成功发货或未发货的发货单
     * 
     * @param   Number    $delivery_id  发货单id
     * @param   Flag      $status       all全部、true已发货、false未发货
     * @param   Number    $parent_id    合并发货单中的父发货单
     * @return array
     */
    public function get_delivery_process($delivery_id, $status='all', $parent_id=0)
    {
        $deliveryObj    = app::get('ome')->model('delivery');
        
        $result     = array();
        $order_id   = 0;
        
        #获取订单order_id
        $order_ids     = $deliveryObj->getOrderIdByDeliveryId($delivery_id);
        foreach ($order_ids as $key => $val)
        {
            $order_id    = $val;
        }
        
        #判断"拆单方式"配置是否变更
        $change_split   = $this->get_split_setup_change($order_id);
        if(!empty($change_split))
        {
            return '';//配置变更，直接回写
        }
        
        #关联的发货单[根据订单order_id获取发货单信息]
        $temp_ids       = $deliveryObj->getDeliverIdByOrderId($order_id);
        if(!empty($temp_ids))
        {
            //去除现操作的delivery_id发货单
            $delivery_ids     = array();
            foreach ($temp_ids as $key => $val)
            {
                if($val == $delivery_id)  continue;
                
                //过滤合并发货单中的父发货单
                if($parent_id && $val == $parent_id)
                {
                    continue;
                }
                
                $delivery_ids[]  = $val;
            }
            
            if(!empty($delivery_ids))
            {
                $cols       = 'delivery_id, delivery_bn, is_cod, logi_id, logi_no, status, branch_id, 
                                 stock_status, deliv_status, expre_status, verify, process, type';
                
                $filter     = array('delivery_id'=>$delivery_ids);
                if($status == 'true')
                {
                    $filter['process'] = 'true';//已发货
                }
                elseif($status == 'false')
                {
                    $filter['process'] = 'false';
                }
                
                $result['delivery']     = $deliveryObj->getList($cols, $filter, 0, -1);
            }
        }
        
        #获取订单是否有未生成的发货单
        if($status == 'false')
        {
            $sql   = "SELECT item_id, order_id, nums, sendnum FROM sdb_ome_order_items WHERE order_id = '".$order_id."' AND nums != sendnum AND `delete` = 'false'";
            $row   = kernel::database()->selectrow($sql);
            $result['order_items'] = $row;
        }
        
        return $result;
    }    
    
    /**
     * 保存_淘宝平台_的原始属性值[bn、oid、quantity、promotion_id]
     * 
     * @param $sdf       订单数据
     * @return Boolean
     */
    public function hold_order_delivery($sdf)
    {
        $data                = array();
        $data['order_bn']    = $sdf['order_bn'];
        
        #现只保存_淘宝平台
        if($sdf['shop_type'] != 'taobao' || empty($sdf['order_objects']))
        {
            return false;
        }
        
        foreach ($sdf['order_objects'] as $key => $obj_val)
        {
            foreach ($obj_val['order_items'] as $key_j => $item)
            {
                $data['oid'][]   = $obj_val['oid'];
                
                $data['bn'][]              = $item['bn'];
                $data['quantity'][]        = $item['quantity'];
                $data['promotion_id'][]    = $item['promotion_id'];
            }
        }
        
        $save_data   = array();
        $save_data['order_bn']   = $data['order_bn'];
        $save_data['oid']            = implode(',', $data['oid']);
        $save_data['quantity']       = implode(',', $data['quantity']);
        $save_data['promotion_id']   = implode(',', $data['promotion_id']);
        
        $save_data['bn']         = serialize($data['bn']);//序列化存储防止有,逗号
        $save_data['dateline']   = time();
        
        $mdl_orddly  = app::get('ome')->model('order_delivery');
        $mdl_orddly->save($save_data);
        
        return true;
    }
    
    /**
     * 判断订单是否已全部发货
     * 
     * @param   String      $order_id        订单号ID
     * @param   String      $delivery_id     发货单ID
     * @param   bool        $is_create_sales 生成销售单单独判断 
     * @return  boolean
     */
    public function check_order_all_delivery($order_id, $delivery_id, $is_create_sales=false)
    {
        #订单"部分拆分"不生成销售单
        $orderObj    = app::get('ome')->model('orders');
        $row         = $orderObj->dump(array('order_id'=>$order_id), 'process_status');
        
        if($row['process_status'] == 'splitting')
        {
            return true;
        }
        
        #判断——订单所属发货单是否全部发货 process!='true'
        $sql    = "SELECT dord.delivery_id, d.delivery_bn, d.process, d.status FROM sdb_ome_delivery_order AS dord
                        LEFT JOIN sdb_ome_delivery AS d ON(dord.delivery_id=d.delivery_id)
                        WHERE dord.order_id='".$order_id."' AND d.delivery_id!='".$delivery_id."' AND d.process!='true'
                        AND (d.parent_id=0 OR d.is_bind='true') AND d.disabled='false'";
        
        #生成销售单时,去除return_back追加发货单状态
        if($is_create_sales)
        {
            $sql    .= " AND d.status NOT IN('failed','cancel','back')";
        }
        else 
        {
            $sql    .= " AND d.status NOT IN('failed','cancel','back','return_back')";
        }
        
        $row    = kernel::database()->selectrow($sql);
        if(!empty($row))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * 余单撤消后_生成销售单
     * 
     * @param   Array     $data     订单号ID
     * @param   Intval    $io       默认0出库
     * @return  boolean
     */
    public function add_to_sales($data, $io=0, $type=null)
    {
        $allow_commit       = false;
        $iostock_instance   = kernel::service('ome.iostock');
        $sales_instance     = kernel::service('ome.sales');
        
        if (method_exists($iostock_instance, 'set') == false)
        {
            return false;
        }
        
        //存储出入库记录
        $iostock_data   = $data['iostock'];
        if(!$type)
        {
             eval('$type='.get_class($iostock_instance).'::LIBRARY_SOLD;');
        }
        
        $iostock_bn     = $iostock_instance->get_iostock_bn($type);
        
        if ( method_exists($sales_instance, 'set') )
        {
            if ($data['sales']['sales_items'])
            {
                $get_order_id       = intval($data['sales']['order_id']);
                $get_delivery_id    = intval($data['sales']['delivery_id']);
                
                #[拆单]获取订单对应所有iostock出入库单
                $order_delivery_iostock_data    = $this->get_delivery_iostock_data($iostock_data);
                
                #多个发货单累加物流成本
                $delivery_cost_actual           = $this->count_delivery_cost_actual($get_order_id);
                if($delivery_cost_actual)
                {
                    $sales_data['delivery_cost_actual']  = $delivery_cost_actual;
                }
                
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
            else
            {
                foreach($data['sales'] as $k=>$v)
                {
                    $get_order_id       = intval($v['order_id']);
                    $get_delivery_id    = intval($v['delivery_id']);
                    
                    #获取订单对应所有iostock出入库单
                    $order_delivery_iostock_data    = $this->get_delivery_iostock_data($iostock_data);
                    
                    #多个发货单累加物流成本
                    $delivery_cost_actual           = $this->count_delivery_cost_actual($get_order_id);
                    if($delivery_cost_actual)
                    {
                        $data['sales'][$k]['delivery_cost_actual']  = $delivery_cost_actual;
                    }
                    
                    //存储销售记录
                    $branch_id = '';
                    if ($data['sales'][$k]['sales_items']){
                        foreach ($data['sales'][$k]['sales_items'] as $kk=>$vv)
                        {
                            #[拆单]多个发货单时_iostock_id为NULL重新获取 ExBOY
                            if(!empty($iostock_data[$vv['item_detail_id']]['iostock_id']))
                            {
                                $vv['iostock_id']   = $iostock_data[$vv['item_detail_id']]['iostock_id'];
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
                    $data['sales'][$k]['operator'] = $data['sales'][$k]['operator'] ? $data['sales'][$k]['operator'] : 'system';
                    if ( $sales_instance->set($data['sales'][$k], $sales_msg) ){
                        $allow_commit = true;
                    }
                }
            }
            
            //更新销售单上的成本单价和成本金额等字段
            kernel::single('tgstockcost_instance_router')->set_sales_iostock_cost($io,$iostock_data);
        }
        
        return $allow_commit;
    }
    
    /**
     * 获取订单对应所有iostock出入库单
     * 
     * @param   Array   $iostock_data     出入库单
     * @param   bool    $is_create_sales 生成销售单单独判断
     * @return  Array
     */
    public function get_delivery_iostock_data($iostock_data, $is_create_sales=false)
    {
        $order_ids  = $delivery_ids = array();
        foreach ($iostock_data as $key => $val)
        {
            $order_ids[$val['order_id']]    = $val['order_id'];
        }
        $in_order_id    = implode(',', $order_ids);
        
        #获取订单对应所有发货单delivery_id
        $sql            = "SELECT dord.delivery_id FROM sdb_ome_delivery_order AS dord LEFT JOIN sdb_ome_delivery AS d ON(dord.delivery_id=d.delivery_id)
                                            WHERE dord.order_id in(".$in_order_id.") AND (d.parent_id=0 OR d.is_bind='true') AND d.disabled='false'";
        
        #生成销售单时,去除return_back追加发货单状态
        if($is_create_sales)
        {
            $sql    .= " AND d.status NOT IN('failed','cancel','back')";
        }
        else 
        {
            $sql    .= " AND d.status NOT IN('failed','cancel','back','return_back')";
        }
        
        $temp_data      = kernel::database()->select($sql);
        foreach ($temp_data as $key => $val)
        {
            $delivery_ids[]     = $val['delivery_id'];
        }
        
        #读取出库记录
        $result     = array();
        $ioObj      = app::get('ome')->model('iostock');
        $field      = 'iostock_id, iostock_bn, type_id, branch_id, original_bn, original_id, original_item_id, bn';
        $temp_data  = $ioObj->getList($field, array('original_id'=>$delivery_ids));
        
        foreach ($temp_data as $key => $val)
        {
            $result[$val['original_item_id']]   = $val;
        }
        
        return $result;
    }
    
    /**
     * 多个发货单累加物流成本
     * 
     * @param   Array   $iostock_data   出入库单
     * @param   bool    $is_create_sales 生成销售单单独判断
     * @return  Array
     */
    public function count_delivery_cost_actual($order_id, $is_create_sales=false)
    {
        $oDelivery      = app::get('ome')->model('delivery');
        $delivery_ids   = $temp_data = array();
        
        #获取订单对应所有发货单delivery_id
        $sql            = "SELECT dord.delivery_id FROM sdb_ome_delivery_order AS dord LEFT JOIN sdb_ome_delivery AS d ON(dord.delivery_id=d.delivery_id)
                                            WHERE dord.order_id='".$order_id."' AND (d.parent_id=0 OR d.is_bind='true') AND d.disabled='false'";
        
        #生成销售单时,去除return_back追加发货单状态
        if($is_create_sales)
        {
            $sql    .= " AND d.status NOT IN('failed','cancel','back')";
        }
        else
        {
            $sql    .= " AND d.status NOT IN('failed','cancel','back','return_back')";
        }
        
        $temp_data      = kernel::database()->select($sql);
        
        #[无拆单]订单只有一个发货单,直接返回false
        if(count($temp_data) < 2)
        {
            return false;
        }
        
        foreach ($temp_data as $key => $val)
        {
            $delivery_ids[]     = $val['delivery_id'];
        }
        
        #累加物流成本
        $dly_data               = $oDelivery->getList('delivery_id, delivery_cost_actual, parent_id, is_bind', array('delivery_id'=>$delivery_ids));
        $delivery_cost_actual   = 0;
        foreach ($dly_data as $key => $val)
        {
            #[合并发货单]重新计算物流运费
            if($val['is_bind'] == 'true')
            {
                $val['delivery_cost_actual']    = $this->compute_delivery_cost_actual($order_id, $val['delivery_id'], $val['delivery_cost_actual']);
            }
            $delivery_cost_actual += floatval($val['delivery_cost_actual']);
        }
        
        return $delivery_cost_actual;
    }
    
    /**
     * 合并发货单_平摊预估物流运费
     * 
     * @param $order_id
     * @param $delivery_id
     * @param  $delivery_cost_actual
     * @return  Array
     */
    public function compute_delivery_cost_actual($order_id, $delivery_id, $delivery_cost_actual)
    {
        $oOrders    = app::get('ome')->model('orders');
        $oDelivery  = app::get('ome')->model('delivery');
        
        $orderIds   = $oDelivery->getOrderIdsByDeliveryIds(array($delivery_id));
        
        $sales_data = $temp_data  = array();
        $temp_data  = $oOrders->getList('order_id, payed', array('order_id'=>$orderIds));
        foreach ($temp_data as $key => $val)
        {
            $val['delivery_cost_actual']    = $delivery_cost_actual;
            $sales_data[$val['order_id']]   = $val;
        }
        
        //平摊预估物流运费，主要处理订单合并发货以及多包裹单的运费问题
        $ome_sales_logistics_feeLib = kernel::single('ome_sales_logistics_fee');
        $ome_sales_logistics_feeLib->calculate($orderIds,$sales_data);
        
        return $sales_data[$order_id]['delivery_cost_actual'];//返回所查订单的平摊物流费用
    }    
    
    /**
     * 获取订单中货品已发货数量
     * 
     * @param Intval $order_id
     * @param Intval $item_id
     * @param Intval $product_id
     * @return Number
     */
    public function get_item_product_num($order_id, $item_id, $product_id)
    {
        if(empty($order_id) || empty($item_id) || empty($product_id))
        {
            return 0;
        }
        
        $sql    = "SELECT SUM(did.number) AS num FROM `sdb_ome_delivery_items_detail` did
                                JOIN `sdb_ome_delivery` d ON d.delivery_id=did.delivery_id
                                WHERE did.order_id='".$order_id."'
                                AND did.order_item_id='".$item_id."'
                                AND did.product_id='".$product_id."'
                                AND d.status != 'back' AND d.status != 'cancel' AND d.status != 'return_back' AND d.is_bind = 'false'";
        $oi    = kernel::database()->selectrow($sql);
        
        return intval($oi['num']);
    }
    
    /**
     * 格式化拆分的订单明细
     * 
     * @param Array $orders 订单数组
     * @param Array $items 订单明细
     * @param Array $splitting_product 拆分的商品数量
     * @return array
     */
    public function format_mkDelivery($orders, $items, $splitting_product)
    {
        if(empty($orders) || empty($items) || empty($splitting_product))
        {
            return '';
        }
        
        #订单上的捆绑商品货号对应的goods_id
        if(count($orders) == 1)
        {
            $orderObjects    = app::get('ome')->model('order_objects');
            
            $order_key     = array_keys($orders);
            $order_id      = $order_key[0];
            $objectList    = $orderObjects->getList('obj_id, bn, quantity', array('order_id'=>$order_id, 'obj_type|in'=>array('pkg', 'giftpackage')));
            if($objectList)
            {
                $objPkgGoods     = app::get('omepkg')->model('pkg_goods');
                
                $order_pkg_list    = array();
                foreach ($objectList as $key => $val)
                {
                    $pkgGoodsRow    = $objPkgGoods->dump(array('pkg_bn'=>$val['bn']), 'goods_id');
                    
                    if(empty($pkgGoodsRow))
                    {
                        continue;
                    }
                    
                    $val['pkg_goods_id']    = $pkgGoodsRow['goods_id'];#PKG捆绑商品多个重复货号时_作为唯一键值
                    $order_pkg_list[$val['obj_id']]    = $val;
                }
            }
        }
        
        #[拆单]只处理选择发货的商品 ExBOY
        $repeat_list    = $repeat_list_ids = array();
        $temp_items     = array();
        
        foreach ($items as $item)
        {
            if(count($orders) == 1)
            {
                $order_id               = $item['order_id'];
                
                $split_item_type        = $item['item_type'];
                $split_product_id       = $item['product_id'];
                $split_item_id          = $item['item_id'];
                $split_item_nums        = $item['nums'];
                
                $temp_items[$split_item_id]['nums']    = $item['nums'];
                
                if($split_item_type == 'pkg' || $split_item_type == 'giftpackage')
                {
                    if($splitting_product[$split_item_type][$split_product_id][$split_item_id])
                    {
                        if(empty($splitting_product[$split_item_type][$split_product_id][$split_item_id]))
                        {
                            $item['nums']    = $split_item_nums - $item['sendnum'];
                        }
                        else
                        {
                            $item['nums']    = intval($splitting_product[$split_item_type][$split_product_id][$split_item_id]);
                        }
                        
                        $item['nums']    = ($item['nums'] > $split_item_nums ? $split_item_nums : $item['nums']);
                        
                        #捆绑商品货号对应的goods_id
                        $item['pkg_goods_id']    = $order_pkg_list[$item['obj_id']]['pkg_goods_id'];
                        
                        $orders[$order_id]['items'][$split_item_id]     = $item;
                    }
                    
                    if($splitting_product[$split_item_type][$split_product_id][$split_item_id])
                    {
                        $repeat_list['pkg'][$split_product_id]['num']++;//判断重复出现的普通商品
                        if($repeat_list['pkg'][$split_product_id]['num'] > 1)
                        {
                            $repeat_list_ids['pkg'][$split_product_id]   = $split_product_id;
                        }
                    }
                }
                else 
                {
                    #防止货品类型为gift、adjunct或其它类型
                    foreach ($splitting_product as $s_type => $s_p_id)
                    {
                        if($s_type != 'pkg' && $s_type != 'giftpackage' && $splitting_product[$s_type][$split_product_id])
                        {
                            #使用拆单拆分的数量
                            $item['nums']    = $splitting_product[$s_type][$split_product_id];
                            
                            #取货品最多可拆分的数量
                            $item['nums']    = ($item['nums'] > $split_item_nums ? $split_item_nums : $item['nums']);
                            
                            $orders[$order_id]['items'][$split_item_id]     = $item;
                        }
                    }
                    
                    $repeat_list['product'][$split_product_id]['num']++;//判断重复出现的普通商品
                    if($repeat_list['product'][$split_product_id]['num'] > 1)
                    {
                        $repeat_list_ids['product'][$split_product_id]   = $split_product_id;
                    }
                }
            }
            else 
            {
                $orders[$item['order_id']]['items'][$item['item_id']] = $item;
            }
        }
        
        #第一种：单个订单审核时
        if(count($orders) == 1)
        {
            #格式化_拆分商品对应的数量
            $split_info    = array();
            foreach ($splitting_product as $item_type => $split_item)
            {
                if($item_type == 'pkg' || $item_type == 'giftpackage')
                {
                    foreach ($split_item as $product_id => $pkg_item)
                    {
                        foreach ($pkg_item as $pkg_item_id => $split_num)
                        {
                            $pkg_goods_id    =  $orders[$order_id]['items'][$pkg_item_id]['pkg_goods_id'];
                            
                            $split_info['pkg'][$product_id][$pkg_goods_id]    = $split_num;
                        }
                    }
                }
                else 
                {
                    foreach ($split_item as $product_id => $split_num)
                    {
                        $split_info['product'][$product_id]    = $split_num;
                    }
                }
            }
            
            #重复货号商品处理
            if($repeat_list_ids)
            {
                foreach ($orders[$order_id]['items'] as $item_id => $item)
                {
                    $split_item_type        = $item['item_type'];
                    $split_product_id       = $item['product_id'];
                    $split_item_id          = $item['item_id'];
                    
                    #gift赠品、adjunct配件_都当作product处理
                    if($split_item_type != 'pkg' && $split_item_type != 'giftpackage')
                    {
                        $split_item_type    = 'product';
                    }
                    
                    #不是重复货号则跳过
                    if(empty($repeat_list_ids[$split_item_type][$split_product_id]))
                    {
                        continue;
                    }
                    
                    #剩余未发货的数量
                    $dly_num    = $this->get_item_product_num($order_id, $split_item_id, $split_product_id);
                    $item_num   = $temp_items[$split_item_id]['nums'] - $dly_num;
                    
                    $orders[$order_id]['items'][$split_item_id]['nums']    = $item_num;
                    
                    #已拆分完数量则过滤掉
                    if($item_num <= 0)
                    {
                        unset($orders[$order_id]['items'][$split_item_id]);
                        continue;
                    }
                    
                    #重复的商品
                    if($split_item_type == 'pkg' || $split_item_type == 'giftpackage')
                    {
                        $pkg_goods_id    = $item['pkg_goods_id'];
                        
                        $split_num       = $split_info['pkg'][$split_product_id][$pkg_goods_id];#拆分的数量
                        
                        if($split_num > 0)
                        {
                            $orders[$order_id]['items'][$split_item_id]['nums']    = ($item_num >= $split_num ? $split_num : $item_num);#允许发货的数量
                            $split_info['pkg'][$split_product_id][$pkg_goods_id]   = $split_num - $item_num;
                        }
                        else
                        {
                            unset($orders[$order_id]['items'][$split_item_id]);#销毁_没有审核数量的item
                        }
                    }
                    else 
                    {
                        $split_num    = $split_info['product'][$split_product_id];
                        if($split_num > 0)
                        {
                            $orders[$order_id]['items'][$split_item_id]['nums']    = ($item_num >= $split_num ? $split_num : $item_num);
                            $split_info['product'][$split_product_id]              = $split_num - $item_num;
                        }
                        else
                        {
                            unset($orders[$order_id]['items'][$split_item_id]);
                        }
                    }
                }
            }
        }
        
        #第二种：多个订单合并审核时(并且其中包含已"部分拆分"的订单)
        if(count($orders) > 1)
        {
            foreach($orders as $order_id => $order)
            {
                if(empty($order['items'])){ continue; }
                
                foreach($order['items'] as $item_id => $item)
                {
                    $dly_num    = $this->get_item_product_num($item['order_id'], $item['item_id'], $item['product_id']);
                    
                    $orders[$order_id]['items'][$item_id]['nums']    = intval($item['nums']) - $dly_num;//剩余未发货数量
                    if($orders[$order_id]['items'][$item_id]['nums'] <= 0)
                    {
                        unset($orders[$order_id]['items'][$item_id]);
                    }
                }
            }
        }
        
        return $orders;
    }    
    
    /**
     * 判断订单是否进行了拆单操作
     * 
     * @param   Number    $delivery_id 发货单id
     * @return  Boolean
     */
    public function check_order_is_split($delivery, $chk_oid=false)
    {
        #获取订单order_id
        $order_id    = intval($delivery['order']['order_id']);
        if(empty($order_id))
        {
            return false;
        }
        
        #获取订单关联的所有发货单id
        $deliveryObj    = app::get('ome')->model('delivery');
        $dly_ids        = $deliveryObj->getDeliverIdByOrderId($order_id);
        
        if(count($dly_ids) > 1)
        {
            return true;
        }
        
        #获取订单是否有未生成的发货单的商品
        $sql   = "SELECT item_id FROM sdb_ome_order_items WHERE order_id = '".$order_id."' AND nums != sendnum AND `delete` = 'false'";
        $row   = kernel::database()->selectrow($sql);
        
        if(!empty($row))
        {
            return true;
        }
        
        #拆单后_余单撤消
        $result     = $this->order_remain_cancel($order_id);
        if($result)
        {
            return true;
        }
        
        return false;
    }    
    
    /**
     * [余单撤消]根据拆单方式进行回写
     * 
     * @param intval $order_id
     * @return boolean
     */
    public function order_remain_cancel($order_id)
    {
        $orderObj    = app::get('ome')->model('orders');
        $row         = $orderObj->dump(array('order_id'=>$order_id), 'process_status');
        
        return ($row['process_status'] == 'remain_cancel' ? true : false);
    }
    
    /**
     * [发货单]获取成功发货的记录
     * 
     * @param $order_id   订单ID
     * @param $out_delivery_id    排除的发货单ID
     * @return array
     */
    public function get_delivery_succ($order_id, $out_delivery_id = 0)
    {
        $sql    = "SELECT dord.delivery_id FROM sdb_ome_delivery_order AS dord LEFT JOIN sdb_ome_delivery AS d 
                    ON(dord.delivery_id=d.delivery_id) WHERE dord.order_id='".intval($order_id)."' AND d.status='succ' AND d.process='true'";
        
        if($out_delivery_id)
        {
            $sql    .= " AND d.delivery_id != '".intval($out_delivery_id)."'";
        }
        
        $data   = kernel::database()->select($sql);
        
        return $data;
    }
    
    /**
     * 获取财务退款确认中申请的商品明细(淘宝、天猫)
     * 
     * @param $order_id
     * @param $bn_data
     * @param $oid_data
     * @return array
     */
    public function getRefundBnList($order_id, $delivery_id, $bn_data, $oid_data)
    {
        if(empty($order_id) || empty($delivery_id) || empty($bn_data) || empty($oid_data))
        {
            return false;
        }
        
        $refund_list          = array();
        $refund_applyObj    = app::get('ome')->model('refund_apply');
        
        #默认只查询15条有商品明细的退款记录
        $refund_array    = $refund_applyObj->getList('product_data', array('order_id'=>$order_id, 'status'=>'4', 'product_data|noequal'=>''), 0, 15);
        
        if(!empty($refund_array))
        {
            foreach ($refund_array as $key => $val)
            {
                $product_data    = unserialize($val['product_data']);
                if(!is_array($product_data))
                {
                    continue;
                }
                
                foreach ($product_data as $item)
                {
                    $refund_list[]    = $item['bn'];
                }
            }
        }
        
        if(!empty($refund_list))
        {
            //发货单明细
            $deliItemModel = app::get('ome')->model('delivery_items');
            $develiy_items = $deliItemModel->getList('product_id, bn, number', array('delivery_id'=>$delivery_id));
            
            //获取购买商品的bn
            $goods_bn     = array();
            foreach($develiy_items as $key => $item)
            {
                $goods_bn[]    = $item['bn'];
            }
            
            #退款上商品明细与发货单商品明细相同时直接返回false_防止回写全部到前端店铺
            if(array_diff($refund_list, $goods_bn))
            {
                return false;
            }
            
            $orderItemModel    = app::get('ome')->model('order_items');
            $orderObjModel     = app::get('ome')->model('order_objects');
            
            $item_list         = $orderItemModel->getList('obj_id', array('order_id'=>$order_id, 'bn'=>$refund_list), 0, -1);
            if(empty($item_list))
            {
                return false;
            }
            $obj_ids    = array();
            foreach ($item_list as $key => $val)
            {
                $obj_ids[]    = $val['obj_id'];
            }
            
            $obj_list        = $orderObjModel->getList('bn', array('order_id'=>$order_id, 'obj_id'=>$obj_ids), 0, -1);
            if(empty($obj_list))
            {
                return false;
            }
            
            $refund_list    = array();
            foreach ($obj_list as $key => $val)
            {
                $refund_list[]    = $val['bn'];
            }
            
            #过滤掉退款的商品明细
            foreach ($refund_list as $refund_bn)
            {
                foreach ($bn_data as $key => $oid_bn)
                {
                    if($refund_bn == $oid_bn)
                    {
                        unset($bn_data[$key], $oid_data[$key]);#删除前端店铺的oid
                    }
                }
            }
        }
        unset($order_id, $refund_array, $refund_list, $product_data, $item_list, $obj_list, $obj_ids);
        
        return array('bn_data'=>$bn_data, 'oid_data'=>$oid_data);
    }    
    
    /**
     * 订单暂停(部分拆分、部分发货)对应多个发货单
     * 
     * @param $order_id
     * @param $must_update
     * @return array
     */
    public function pauseOrder_split($order_id, $must_update = 'false')
    {
        $flag       = false;//标记有未能暂停的发货单
        $pause_dly  = array();//已暂停OR取消的发货单
        $rs         = array();
        
        $orderObj    = app::get('ome')->model('orders');
        $dlyObj      = app::get('ome')->model("delivery");
        $oOperation_log    = app::get('ome')->model('operation_log');
        
        if ($order_id){
            $o = $orderObj->dump($order_id,'pause, shop_id');
            
            if ($o['pause'] == 'false' || $must_update == 'true')
            {
                $branchLib = kernel::single('ome_branch');
                $channelLib = kernel::single('channel_func');
                $eventLib = kernel::single('ome_event_trigger_delivery');
                
                $delivery_itemsObj = app::get('ome')->model('delivery_items');
                $branch_productObj = app::get('ome')->model('branch_product');
                
                $shopFreezeLib    = kernel::single('ome_shop_freeze');
                $shopFreezeLogLib    = kernel::single('ome_shop_freeze_log');
                
                #获取店铺冻结系统配置
                $is_shop_freeze_log    = $shopFreezeLogLib->get_product_shop_freeze_config();
                
                //查询订单是否有发货单
                $dly_ids    = $dlyObj->getDeliverIdByOrderId($order_id);
                if($dly_ids)
                {
                    //处理订单对应多个发货单
                    foreach ($dly_ids as $key => $delivery_id)
                    {
                        //取仓库信息
                        $deliveryInfo = $dlyObj->dump($delivery_id,'*');
                        
                        #[自有仓]OR[第三方仓]已发货的发货单不执行
                        if($deliveryInfo['status'] == 'succ')
                        {
                            continue;
                        }
                        
                        $wms_id = $branchLib->getWmsIdById($deliveryInfo['branch_id']);
                        if($wms_id){
                            $is_selfWms = $channelLib->isSelfWms($wms_id);//是否自有仓储
                            if($is_selfWms)
                            {
                                $res = $eventLib->pause($wms_id,array('outer_delivery_bn'=>$deliveryInfo['delivery_bn']),true);

                                if($res['rsp'] == 'success' || $res['rsp'] == 'succ')
                                {
                                    #[拆单]保存成功暂停的发货单 ExBOY
                                    $deliveryInfo['is_selfwms'] = true;
                                    $pause_dly[]                = $deliveryInfo;
                                }else{
                                    $rs[$delivery_id]['msg'] = $res['msg'];
                                    $rs[$delivery_id]['rsp']= 'fail';
                                    
                                    $rs[$delivery_id]['bn']   = $deliveryInfo['delivery_bn'];
                                    $rs[$delivery_id]['flag'] = 'self_wms';
                                    $flag   = true;//标记
                                }
                            }else{
                                $res = $eventLib->cancel($wms_id,array('outer_delivery_bn'=>$deliveryInfo['delivery_bn']),true);
                                
                                if($res['rsp'] == 'success' || $res['rsp'] == 'succ')
                                {
                                    #[拆单]保存成功取消的发货单 ExBOY
                                    $deliveryInfo['is_selfwms'] = false;
                                    $pause_dly[]                = $deliveryInfo;
                                    $oOperation_log->write_log('delivery_back@ome',$deliveryInfo['delivery_id'],'发货单取消成功');
                                }else{
                                    $rs[$delivery_id]['rsp'] = 'fail';
                                    $rs[$delivery_id]['msg'] = $res['msg'];
                                    
                                    $oOperation_log->write_log('delivery_back@ome',$deliveryInfo['delivery_id'],'发货单取消失败,原因:'.$rs['msg']);
                                    
                                    $rs[$delivery_id]['bn']   = $deliveryInfo['delivery_bn'];
                                    $rs[$delivery_id]['flag'] = 'wms';
                                    $flag   = true;//标记
                                    $dlyObj->update_sync_cancel($deliveryInfo['delivery_id'],'fail'); 
                                }
                            }
                        }else{
                            $rs[$delivery_id]['rsp'] = 'fail';
                            
                            $rs[$delivery_id]['bn']   = $deliveryInfo['delivery_bn'];
                            $rs[$delivery_id]['flag'] = 'none_wms';
                            $flag   = true;//标记
                        }
                    }
                }else{
                    //没有发货单的情况，直接暂停当前订单
                    $order['order_id'] = $order_id;
                    $order['pause'] = 'true';
                    $orderObj->save($order);
                    $oOperation_log->write_log('order_modify@ome',$order_id,'订单暂停');

                    //订单暂停状态同步
                    if ($service_order = kernel::servicelist('service.order')){
                        foreach($service_order as $object=>$instance){
                            if(method_exists($instance, 'update_order_pause_status')){
                                $instance->update_order_pause_status($order_id);
                            }
                        }
                    }
                    
                    $rs = array('rsp'=>'succ','msg'=>'');
                    return $rs;
                }
            }
        }
        
        #[拆单]发货单全部成功发货
        if(!empty($dly_ids) && empty($pause_dly) && $flag == false)
        {
            $order_id_list    = array();
            
            //处理订单对应多个发货单
            foreach ($dly_ids as $key => $delivery_id)
            {
                //取仓库信息
                $deliveryInfo = $dlyObj->dump($delivery_id,'delivery_id, is_bind');
                
                //是否是合并发货单
                if($deliveryInfo['is_bind'] == 'true')
                {
                    //取关联订单号进行暂停
                    $order_ids = $dlyObj->getOrderIdByDeliveryId($deliveryInfo['delivery_id']);
                    if($order_ids){
                        foreach ($order_ids as $id){
                            $order_id_list[]    = $id;
                        }
                    }
                }
            }
            $order_id_list[]    = $order_id;
            $order_id_list      = array_unique($order_id_list);
            
            foreach ($order_id_list as $id){
                $order['order_id'] = $id;
                $order['pause'] = 'true';
                $orderObj->save($order);
                $oOperation_log->write_log('order_modify@ome',$id,'订单暂停');
            }
            
            //订单暂停状态同步
            if ($service_order = kernel::servicelist('service.order'))
            {
                foreach($service_order as $object=>$instance)
                {
                    if(method_exists($instance, 'update_order_pause_status'))
                    {
                        foreach ($order_id_list as $id)
                        {
                            $instance->update_order_pause_status($id);
                        }
                    }
                }
            }
            
            $rs = array('rsp'=>'succ','msg'=>'');
            return $rs;
        }
        
        #[全部]成功暂停OR取消的发货单_则执行
        if($pause_dly && $flag == false)
        {
            $deliveryInfo   = array();
            foreach ($pause_dly as $key => $val)
            {
                $deliveryInfo   = $val;
                
                //自有仓储
                if($deliveryInfo['is_selfwms'] == true)
                {
                    //wms暂停发货单成功，暂停本地主发货单
                    $tmpdly = array(
                        'delivery_id' => $deliveryInfo['delivery_id'],
                        'pause' => 'true'
                    );
                    $dlyObj->save($tmpdly);
                    $oOperation_log->write_log('delivery_modify@ome',$deliveryInfo['delivery_id'],'发货单暂停');

                    //是否是合并发货单
                    if($deliveryInfo['is_bind'] == 'true'){
                        //取关联发货单号进行暂停
                        $delivery_ids = $dlyObj->getItemsByParentId($deliveryInfo['delivery_id'],'array');
                        if($delivery_ids){
                            foreach ($delivery_ids as $id){
                                $tmpdly = array(
                                    'delivery_id' => $id,
                                    'pause' => 'true'
                                );
                                $dlyObj->save($tmpdly);
                                $oOperation_log->write_log('delivery_modify@ome',$id,'发货单暂停');
                            }
                        }
                        
                        //取关联订单号进行暂停
                        $order_ids = $dlyObj->getOrderIdByDeliveryId($deliveryInfo['delivery_id']);
                        if($order_ids){
                            foreach ($order_ids as $id){
                                $order['order_id'] = $id;
                                $order['pause'] = 'true';
                                $orderObj->save($order);
                                $oOperation_log->write_log('order_modify@ome',$id,'订单暂停');
                            }
                        }
                    }else{
                        //暂停当前订单
                        $order['order_id'] = $order_id;
                        $order['pause'] = 'true';
                        $orderObj->save($order);
                        $oOperation_log->write_log('order_modify@ome',$order_id,'订单暂停');
                    }
                    
                    //订单暂停状态同步
                    if ($service_order = kernel::servicelist('service.order')){
                        foreach($service_order as $object=>$instance){
                            if(method_exists($instance, 'update_order_pause_status')){
                               if($order_ids){
                                   foreach ($order_ids as $id){
                                       $instance->update_order_pause_status($id);
                                   }
                               }else{
                                   $instance->update_order_pause_status($order_id);
                               }
                            }
                        }
                    }
                }
                //第三方仓储
                else 
                {
                    //wms第三方仓储取消发货单成功，本地主发货单取消
                    $tmpdly = array(
                        'delivery_id' => $deliveryInfo['delivery_id'],
                        'status' => 'cancel',
                        'logi_id' => NULL,
                        'logi_name' => '',
                        'logi_no' => NULL,
                    );
                    $dlyObj->save($tmpdly);
                    $oOperation_log->write_log('delivery_modify@ome',$deliveryInfo['delivery_id'],'发货单撤销成功');
    
                    //是否是合并发货单
                    if($deliveryInfo['is_bind'] == 'true'){
                        //取关联发货单号进行暂停
                        $delivery_ids = $dlyObj->getItemsByParentId($deliveryInfo['delivery_id'],'array');
                        if($delivery_ids){
                            foreach ($delivery_ids as $id){
                                $tmpdly = array(
                                    'delivery_id' => $id,
                                    'status' => 'cancel',
                                    'logi_id' => NULL,
                                    'logi_name' => '',
                                    'logi_no' => NULL,
                                );
                                $dlyObj->save($tmpdly);
                                $oOperation_log->write_log('delivery_modify@ome',$id,'发货单撤销成功');
                            }
                        }
    
                        //取关联订单号进行还原
                        $order_ids = $dlyObj->getOrderIdByDeliveryId($deliveryInfo['delivery_id']);
                        if($order_ids){
                            foreach ($order_ids as $id){
                                $order['order_id'] = $id;
                                $order['confirm'] = 'N';
                                $order['process_status'] = 'unconfirmed';
                                
                                #[拆单]获取订单对应有效的发货单
                                $temp_dlyid     = $dlyObj->getDeliverIdByOrderId($id);
                                if(!empty($temp_dlyid))
                                {
                                    $order['process_status'] = 'splitting';//部分拆分
                                }
                                
                                $orderObj->save($order);
                                $oOperation_log->write_log('order_modify@ome',$id,'发货单撤销,订单还原需重新审核');
                            }
                        }
                    }else{
                        //还原当前订单
                        $order['order_id'] = $order_id;
                        $order['confirm'] = 'N';
                        $order['process_status'] = 'unconfirmed';
                        
                        #[拆单]获取订单对应有效的发货单
                        $temp_dlyid     = $dlyObj->getDeliverIdByOrderId($order_id);
                        if(!empty($temp_dlyid))
                        {
                            $order['process_status'] = 'splitting';//部分拆分
                        }
                        
                        $orderObj->save($order);
                        $oOperation_log->write_log('order_modify@ome',$order_id,'发货单撤销,订单还原需重新审核');
                    }
    
                    //释放冻结库存
                     //增加branch_product释放冻结库存
                    $branch_id = $deliveryInfo['branch_id'];
                    $product_ids = $delivery_itemsObj->getList('product_id,number',array('delivery_id'=>$deliveryInfo['delivery_id']),0,-1);
                    foreach($product_ids as $key=>$v){
                        $branch_productObj->unfreez($branch_id,$v['product_id'],$v['number']);
                        
                        #[增加]货品店铺冻结
                        $shopFreezeLib->freeze($o['shop_id'], $v['product_id'], $v['number']);
                        
                        #店铺冻结增减明细日志
                        if($is_shop_freeze_log)
                        {
                            $shopFreezeLogLib->changeLog($o['shop_id'], $v['product_id'], $v['number'], 4, '[拆单]第三方仓储取消发货单号:'. $deliveryInfo['delivery_bn'] .',增加店铺冻结');
                        }
                    }
                }
            }   
        }
        
        #失败结果处理 
        if($flag)
        {
            $temp_rs    = array('rsp'=>'fail', 'is_split'=>'true');
            foreach ($rs as $key => $val)
            {
                $temp_rs['msg']     .= '发货单'.$val['bn'].' '.str_replace('数字校验失败', '撤销失败', $val['msg']).'<br>';
            }
            
            #成功暂停或取消的发货单
            if(!empty($pause_dly))
            {
                $temp_msg   = array();
                foreach ($pause_dly as $key => $val)
                {
                    if($val['is_selfwms'] == true)
                    {
                        $temp_msg['is_selfwms'][]   = $val['delivery_bn'];
                    }
                    else 
                    {
                        $temp_msg['other'][]   = $val['delivery_bn'];
                    }
                }
                
                if(!empty($temp_msg['is_selfwms']))
                {
                    $temp_rs['msg'] .= '<br><br>自有仓储,成功暂停的发货单：'.implode(',', $temp_msg['is_selfwms']);
                }
                if(!empty($temp_msg['other']))
                {
                    $temp_rs['msg'] .= '<br><br>第三方仓储,成功取消的发货单：'.implode(',', $temp_msg['is_selfwms']);
                }
            }
            
            $rs = $temp_rs;
            unset($temp_rs);
        }
        else 
        {
            $rs = array('rsp'=>'succ','msg'=>'');
        }
        
        return $rs;
    }
    
    /**
     * 订单恢复(部分拆分、部分发货)对应多个发货单
     * 
     * @param unknown $order_id
     * @return boolean
     */
    public function renewOrder_split($order_id)
    {
        $flag   = false;//标记有未能暂停的发货单
        $pause_dly  = array();//需要恢复的发货单
        
        $orderObj    = app::get('ome')->model('orders');
        $dlyObj      = app::get('ome')->model("delivery");
        $oOperation_log    = app::get('ome')->model('operation_log');
        
        if ($order_id){
            $o = $orderObj->dump($order_id,'pause');

            if ($o['pause'] == 'true')
            {
                $branchLib = kernel::single('ome_branch');
                $channelLib = kernel::single('channel_func');
                $eventLib = kernel::single('ome_event_trigger_delivery');
                
                //查询订单是否有发货单
                $dly_ids = $dlyObj->getDeliverIdByOrderId($order_id);
                if($dly_ids)
                {
                    //处理订单对应多个发货单
                    foreach ($dly_ids as $key => $delivery_id)
                    {
                        //取仓库信息
                        $deliveryInfo = $dlyObj->dump($delivery_id,'*');
                        
                        #[自有仓]OR[第三方仓]已发货的发货单不执行
                        if($deliveryInfo['status'] == 'succ')
                        {
                            continue;
                        }
                        $pause_dly[]    = $deliveryInfo;
                        
                        $wms_id = $branchLib->getWmsIdById($deliveryInfo['branch_id']);
                        if($wms_id){
                            $is_selfWms = $channelLib->isSelfWms($wms_id);
                            if($is_selfWms){
                                $res = $eventLib->renew($wms_id,array('outer_delivery_bn'=>$deliveryInfo['delivery_bn']),true);
                                if($res['rsp'] == 'success' || $res['rsp'] == 'succ'){
                                    //wms恢复发货单成功，恢复本地主发货单
                                    $tmpdly = array(
                                        'delivery_id' => $deliveryInfo['delivery_id'],
                                        'pause' => 'false'
                                    );
                                    $dlyObj->save($tmpdly);
                                    $oOperation_log->write_log('delivery_modify@ome',$deliveryInfo['delivery_id'],'发货单恢复');
                                    
                                    //是否是合并发货单
                                    if($deliveryInfo['is_bind'] == 'true'){
                                        //取关联发货单号进行暂停
                                        $delivery_ids = $dlyObj->getItemsByParentId($deliveryInfo['delivery_id'],'array');
                                        if($delivery_ids){
                                            foreach ($delivery_ids as $id){
                                                $tmpdly = array(
                                                    'delivery_id' => $id,
                                                    'pause' => 'false'
                                                );
                                                $dlyObj->save($tmpdly);
                                                $oOperation_log->write_log('delivery_modify@ome',$id,'发货单恢复');
                                            }
                                        }
                                        
                                        //取关联订单号进行暂停
                                        $order_ids = $dlyObj->getOrderIdByDeliveryId($deliveryInfo['delivery_id']);
                                        if($order_ids){
                                            foreach ($order_ids as $id){
                                                $order['order_id'] = $id;
                                                $order['pause'] = 'false';
                                                $orderObj->save($order);
                                                $oOperation_log->write_log('order_modify@ome',$id,'订单恢复');
                                            }
                                        }
                                    }else{
                                        //暂停当前订单
                                        $order['order_id'] = $order_id;
                                        $order['pause'] = 'false';
                                        $orderObj->save($order);
                                        $oOperation_log->write_log('order_modify@ome',$order_id,'订单恢复');
                                    }
    
                                    //订单暂停状态同步
                                    if ($service_order = kernel::servicelist('service.order')){
                                        foreach($service_order as $object=>$instance){
                                            if(method_exists($instance, 'update_order_pause_status')){
                                               if($order_ids){
                                                   foreach ($order_ids as $id){
                                                       $instance->update_order_pause_status($id, 'false');
                                                   }
                                               }else{
                                                   $instance->update_order_pause_status($order_id, 'false');
                                               }
                                            }
                                        }
                                    }
                                }else{
                                    $flag   = true;
                                }
                            }
                        }else{
                            $flag   = true;
                        }
                    }
                }else{
                    $order['order_id'] = $order_id;
                    $order['pause'] = 'false';
                    $orderObj->save($order);
                    $oOperation_log->write_log('order_modify@ome',$order_id,'订单恢复');

                    //订单恢复状态同步
                    if ($service_order = kernel::servicelist('service.order')){
                        foreach($service_order as $object=>$instance){
                            if(method_exists($instance, 'update_order_pause_status')){
                               $instance->update_order_pause_status($order_id, 'false');
                            }
                        }
                    }
                }
                
                #[拆单]发货单全部成功发货
                if(!empty($dly_ids) && empty($pause_dly) && $flag == false)
                {
                    $order_id_list    = array();
                    
                    //处理订单对应多个发货单
                    foreach ($dly_ids as $key => $delivery_id)
                    {
                        //取仓库信息
                        $deliveryInfo = $dlyObj->dump($delivery_id,'delivery_id, is_bind');
                        
                        //是否是合并发货单
                        if($deliveryInfo['is_bind'] == 'true')
                        {
                            //取关联订单号进行暂停
                            $order_ids = $dlyObj->getOrderIdByDeliveryId($deliveryInfo['delivery_id']);
                            if($order_ids){
                                foreach ($order_ids as $id){
                                    $order_id_list[]    = $id;
                                }
                            }
                        }
                    }
                    $order_id_list[]    = $order_id;
                    $order_id_list      = array_unique($order_id_list);
                    
                    foreach ($order_id_list as $id){
                        $order['order_id'] = $id;
                        $order['pause'] = 'false';
                        $orderObj->save($order);
                        $oOperation_log->write_log('order_modify@ome',$id,'订单恢复');
                    }
                    
                    //订单恢复状态同步
                    if ($service_order = kernel::servicelist('service.order'))
                    {
                        foreach($service_order as $object=>$instance)
                        {
                            if(method_exists($instance, 'update_order_pause_status'))
                            {
                                foreach ($order_id_list as $id)
                                {
                                    $instance->update_order_pause_status($id);
                                }
                            }
                        }
                    }
                }
                
                return ($flag ? false : true);
            }
        }
        return false;
    }
    
    /**
     * 打回订单对应的发货单
     * 
     * @param  $order_id
     * @param  $dly_status  只打回未发货的发货单
     * @return boolean
     */
    public function rebackDeliveryByOrderId_split($order_id, $dly_status=false)
    {
        $flag   = true;//[拆单]发货单打回_成功标志 ExBOY
        
        $dlyObj      = app::get('ome')->model("delivery");
        $dly_oObj    = app::get('ome')->model("delivery_order");
        $opObj       = app::get('ome')->model('operation_log');
        
        $data = $dly_oObj->getList('*',array('order_id'=>$order_id),0,-1);
        $bind = array();
        $dlyos = array();
        $mergedly = array();
        
        if ($data)
        foreach ($data as $v){
            $dly = $dlyObj->dump($v['delivery_id'],'process,status,parent_id,is_bind');
            //只打回未发货的发货单
            if ($dly_status == true){
                if ($dly['process'] == 'true' || in_array($dly['status'],array('failed', 'cancel', 'back', 'succ','return_back'))) continue;
            }
            if ($dly['parent_id'] == 0 && $dly['is_bind'] == 'true'){
                $bind[$v['delivery_id']]['delivery_id'] = $v['delivery_id'];
            }elseif ($dly['parent_id'] == 0){
                $dlyos[$v['delivery_id']][] = $v['delivery_id'];
            }else{
                $mergedly[$v['delivery_id']] = $v['delivery_id'];
                $bind[$dly['parent_id']]['items'][] = $v['delivery_id'];
            }
        }
        
        //如果是合并发货单的话
        if ($bind)
        foreach ($bind as $k => $i){
            $items = $dlyObj->getItemsByParentId($i['delivery_id'], 'array', 'delivery_id');
            
            #拆分发货单
            $result = $dlyObj->splitDelivery($i['delivery_id'], $i['items']);
            
            if ($result){
                $flag   = $dlyObj->rebackDelivery($i['items'], '', $dly_status);
                
                #打回发货单失败_退出
                if($flag == false)
                {
                    foreach ($i['items'] as $i){
                        $opObj->write_log('delivery_back@ome', $i ,'发货单打回失败');
                    }
                    return false;
                }
                
                foreach ($i['items'] as $i){
                    $opObj->write_log('delivery_back@ome', $i ,'发货单打回');
                    $dlyObj->updateOrderPrintFinish($i, 1);
                }
            }
        }
        
        //单个发货单
        if ($dlyos)
        foreach ($dlyos as $v){
            $flag   = $dlyObj->rebackDelivery($v, '', $dly_status);
            
            #打回发货单失败_退出
            if($flag == false)
            {
                $opObj->write_log('delivery_back@ome', $v ,'发货单打回失败');
                return false;
            }
            
            $opObj->write_log('delivery_back@ome', $v ,'发货单打回');
            $dlyObj->updateOrderPrintFinish($v, 1);
        }
        
        return true;
    }
}
