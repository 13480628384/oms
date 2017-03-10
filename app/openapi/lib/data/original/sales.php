<?php

class openapi_data_original_sales{
    public function getList($filter,$start_time,$end_time,$offset=0,$limit=100){
        if(empty($start_time) || empty($end_time)){
            return false;
        }
        $sqlstr = '';
        $shopObj = app::get('ome')->model('shop');
        if ($filter['shop_bn']) {
            $shop_bn = explode('#',$filter['shop_bn']);
            $shop_id = $shopObj->getlist('shop_id',array('shop_bn|in'=>$shop_bn));
            foreach ($shop_id as $value) {
                $shop_ids[]=$value['shop_id'];
            }
            $shopIds = "'".implode("','",$shop_ids)."'";
            $sqlstr.=" AND shop_id in(".$shopIds.")";
        }
        $formatFilter=kernel::single('openapi_format_abstract');
        $countList = kernel::database()->selectrow("select count(sale_id) as _count from sdb_ome_sales where sale_time >=".$start_time." and sale_time <".$end_time.$sqlstr);
        
        if(intval($countList['_count']) >0){
            $branchObj = app::get('ome')->model('branch');
            $orderObj = app::get('ome')->model('orders');
            $memberObj = app::get('ome')->model('members');
            $deliveryObj = app::get('ome')->model('delivery');
            $opObj = app::get('desktop')->model('users');
            $productObj = app::get('ome')->model('products');
            $shopInfos = array();
            $shop_arr = $shopObj->getList('shop_id,shop_bn,name', array(), 0, -1);
            foreach ($shop_arr as $k => $shop){
                $shopInfos[$shop['shop_id']] = $shop;
            }

            $branchInfos = array();
            $branch_arr = $branchObj->getList('branch_id,name,branch_bn', array(), 0, -1);
            foreach ($branch_arr as $k => $branch){
                $branchInfos[$branch['branch_id']]['name'] = $branch['name'];
                $branchInfos[$branch['branch_id']]['branch_bn'] = $branch['branch_bn'];
            }

            $saleLists = kernel::database()->select("select * from sdb_ome_sales where sale_time >=".$start_time." and sale_time <".$end_time.$sqlstr." order by sale_time asc limit ".$offset.",".$limit."");

            $saleIds = array();
            $orderIds = array();
            $memberIds = array();
            $deliveryIds = array();
            $opIds = array();
            foreach ($saleLists as $k => $sale){
                $saleIds[] = $sale['sale_id'];

                if(intval($sale['order_id'])>0 && !in_array($sale['order_id'],$orderIds)){
                    $orderIds[] =  $sale['order_id'];
                }

                if(intval($sale['member_id'])>0 && !in_array($sale['member_id'],$memberIds)){
                    $memberIds[] = $sale['member_id'];
                }

                if(intval($sale['delivery_id'])>0 && !in_array($sale['delivery_id'],$deliveryIds)){
                    $deliveryIds[] = $sale['delivery_id'];
                }

                if(intval($sale['order_check_id'])>0 && !in_array($sale['order_check_id'],$opIds)){
                    $opIds[] = $sale['order_check_id'];
                }
            }

            $order_arr = $orderObj->getList('order_id,order_bn,mark_text,tax_company',array('order_id'=>$orderIds),0,-1);
            foreach ($order_arr as $k => $order){
                $orderInfos[$order['order_id']]['order_bn'] = $order['order_bn'];
                //if ($order['mark_text']) {

                $orderInfos[$order['order_id']]['order_memo'] = $order['mark_text'] ? $this->get_mark_text($order['mark_text']) : '';
                //}
                $orderInfos[$order['order_id']]['tax_title'] = $order['tax_company'];
            }

            $member_arr = $memberObj->getList('member_id,name',array('member_id'=>$memberIds),0,-1);
            foreach ($member_arr as $k => $member){
                $memberInfos[$member['member_id']] = $member['name'];
            }

            $delivery_arr = $deliveryObj->getList('delivery_id,delivery_bn,ship_name,ship_area,ship_province,ship_city,ship_district,ship_addr,ship_zip,ship_tel,ship_mobile,ship_email,logi_name,logi_no,weight,delivery_cost_actual',array('delivery_id'=>$deliveryIds),0,-1);
            foreach ($delivery_arr as $k => $delivery){
                $deliveryInfos[$delivery['delivery_id']] = $delivery;
            }

            $op_arr = $opObj->getList('user_id,name',array('user_id'=>$opIds),0,-1);
            foreach ($op_arr as $k => $op){
                $opInfos[$op['user_id']] = $op['name'];
            }            
            $saleInfos = array();
            foreach ($saleLists as $k => $sale){
                $sql = 'select ODB.logi_no from sdb_ome_delivery_bill as ODB left join sdb_ome_delivery_order as ODO on ODB.delivery_id = ODO.delivery_id left join sdb_ome_sales as OS on OS.order_id= ODO.order_id where OS.sale_id= '.$sale['sale_id'];
                $delivery_bill = kernel::database()->select($sql);
                if($delivery_bill){
                    foreach ($delivery_bill as $value){
                        $sale['logi_no'] .='|'.$value['logi_no'];
                    }
                }
                $saleInfos[$sale['sale_id']] = $sale;
                $saleInfos[$sale['sale_id']]['order_bn'] = $orderInfos[$sale['order_id']]['order_bn'];
                $saleInfos[$sale['sale_id']]['shop_bn'] = $shopInfos[$sale['shop_id']]['shop_bn'];
                $saleInfos[$sale['sale_id']]['shop_name'] = $shopInfos[$sale['shop_id']]['name'];
                $saleInfos[$sale['sale_id']]['branch_name'] = $branchInfos[$sale['branch_id']]['name'];
                $saleInfos[$sale['sale_id']]['branch_bn'] = $branchInfos[$sale['branch_id']]['branch_bn'];
                $saleInfos[$sale['sale_id']]['member_name'] = $memberInfos[$sale['member_id']];
                $saleInfos[$sale['sale_id']]['delivery_bn'] = $deliveryInfos[$sale['delivery_id']]['delivery_bn'];
                $saleInfos[$sale['sale_id']]['ship_name'] = $deliveryInfos[$sale['delivery_id']]['ship_name'];
                $saleInfos[$sale['sale_id']]['ship_area'] = $deliveryInfos[$sale['delivery_id']]['ship_province'].'-'.$deliveryInfos[$sale['delivery_id']]['ship_city'].'-'.$deliveryInfos[$sale['delivery_id']]['ship_district'];
                $saleInfos[$sale['sale_id']]['ship_addr'] = $deliveryInfos[$sale['delivery_id']]['ship_addr'];
                $saleInfos[$sale['sale_id']]['ship_zip'] = $deliveryInfos[$sale['delivery_id']]['ship_zip'];
                $saleInfos[$sale['sale_id']]['ship_tel'] = $deliveryInfos[$sale['delivery_id']]['ship_tel'];
                $saleInfos[$sale['sale_id']]['ship_mobile'] = $deliveryInfos[$sale['delivery_id']]['ship_mobile'];
                $saleInfos[$sale['sale_id']]['ship_email'] = $deliveryInfos[$sale['delivery_id']]['ship_email'];
                $saleInfos[$sale['sale_id']]['order_check_name'] = $opInfos[$sale['order_check_id']];
                //新增物流公司、物流单号、包裹重量、订单备注、物流费 
                $saleInfos[$sale['sale_id']]['logi_name'] = $formatFilter->charFilter($deliveryInfos[$sale['delivery_id']]['logi_name']);
                $saleInfos[$sale['sale_id']]['logi_no'] = $formatFilter->charFilter($deliveryInfos[$sale['delivery_id']]['logi_no']);
                $saleInfos[$sale['sale_id']]['weight'] = $formatFilter->charFilter($deliveryInfos[$sale['delivery_id']]['weight']);
                $saleInfos[$sale['sale_id']]['delivery_cost_actual'] = $formatFilter->charFilter($deliveryInfos[$sale['delivery_id']]['delivery_cost_actual']);
                $saleInfos[$sale['sale_id']]['order_memo'] = $formatFilter->charFilter($orderInfos[$sale['order_id']]['order_memo']);
                $saleInfos[$sale['sale_id']]['tax_title'] = $formatFilter->charFilter($orderInfos[$sale['order_id']]['tax_title']);
                //
                $saleInfos[$sale['sale_id']]['sale_items'] = array();
            }

            if(count($saleIds) == 1){
                $_where_sql = " sale_id =".$saleIds[0]."";
            }else{
                $_where_sql = " sale_id in(".implode(',', $saleIds).")";
            }

            $sale_items = kernel::database()->select("select * from sdb_ome_sales_items where ".$_where_sql."");
            foreach ($sale_items as $k =>$sale_item){
                if(isset($saleInfos[$sale_item['sale_id']])){
                    // 判断是普通商品还是捆绑商品
                    if ($sale_item['product_id']) {
                        $sale_item['item_type'] = 'product';
                    } else {
                        $is_pkg = kernel::single('omepkg_ome_product')->checkProductByBn($sale_item['bn']);
                        if ($is_pkg) {
                            $sale_item['item_type'] = 'pkg';
                        }
                    }
                    $product = $productObj->dump(array('product_id'=>$sale_item['product_id']),'barcode');

                    $sale_item['barcode'] = $product['barcode'];
                    $saleInfos[$sale_item['sale_id']]['sale_items'] = array_merge($saleInfos[$sale_item['sale_id']]['sale_items'],array($sale_item));
                }
            }
           return array(
                'lists' => $saleInfos,
                'count' => $countList['_count'],
            );
        }else{
            return array(
                'lists' => array(),
                'count' => 0,
            );
        }
    }
    
    /**
     * 返回备注
     * @param
     * @return
     * @access  public
     * @author sunjing@shopex.cn
     */
    function get_mark_text($mark_text)
    {
        $mark = unserialize($mark_text);
        $memo = '';
        if (is_array($mark) || !empty($mark)){
           $memo = array_pop($mark);
        }
        return $memo['op_content'];
    }

    public function SalesAmount($start_time,$end_time,$offset=0,$limit=100,$shop_bn = false){
        if(empty($start_time) || empty($end_time)){
            return false;
        }
        $shopObj = app::get('ome')->model('shop');
        $shop_arr = $shopObj->getList('shop_id,shop_bn', array(), 0, -1);
        foreach ($shop_arr as $k => $_shop){
            $shopInfos["'".$_shop['shop_id']."'"] = $_shop;
        }
        $str_shop_id = null;
        if(!empty($shop_bn)){
            foreach ($shop_bn as $k => $_shop_bn){
                $shop_info = $shopObj->getList('shop_id,shop_bn,name', array('shop_bn'=>$_shop_bn));
                if(!empty($shop_info)){
                    $all_shop_id[] = $shop_info[0]['shop_id'];
                }
            }
            if(!empty($all_shop_id)){
                foreach($all_shop_id as $v){
                    if(trim($v)){
                        $shop_id[] = "'".trim($v)."'";
                    }
                }
                $str_shop_id = implode(',',$shop_id);
            }else{
                #传了店铺编码参数，但是店铺编码有误的
                return array('lists' => array());
            }
        }
        if(empty($str_shop_id)){
            $sql = "select count(sale_id) as _count from sdb_ome_sales where sale_time >=".$start_time." and sale_time <".$end_time; 
            $countList = kernel::database()->selectrow( $sql);
        }else{
            $sql = "select count(sale_id) as _count from sdb_ome_sales where sale_time >=".$start_time." and sale_time <".$end_time.' and shop_id in('.$str_shop_id .')';
            $countList = kernel::database()->selectrow($sql);
        }
        if(intval($countList['_count']) >0){
            if(empty($str_shop_id)){
                $saleLists = kernel::database()->select("select sale_id from sdb_ome_sales where sale_time >=".$start_time." and sale_time <".$end_time." order by sale_time asc limit ".$offset.",".$limit."");
            }else{
                $saleLists = kernel::database()->select("select sale_id from sdb_ome_sales where sale_time >=".$start_time." and sale_time <".$end_time.' and shop_id in('.$str_shop_id .')'." order by sale_time asc limit ".$offset.",".$limit."");
            }
            $saleIds = array();
            foreach ($saleLists as $k => $sale){
                $saleIds[] = $sale['sale_id'];
            }
            
            if(count($saleIds) == 1){
                $_where_sql = " sales.sale_id =".$saleIds[0]."";
            }else{
                $_where_sql = " sales.sale_id in(".implode(',', $saleIds).")";
            }
           /*  $sql = "select
                        sales.shop_id,sum(items.sales_amount) sales_amount
                    from sdb_ome_sales sales
                    left join sdb_ome_sales_items items
                    on  sales.sale_id=items.sale_id
                    where ".$_where_sql." group by sales.shop_id order by null"; */
            
            $sql = "select
                        shop_id,sum(sale_amount) sales_amount,sum(cost_freight) cost_freight,sum(discount) discount,sum(additional_costs) additional_costs
                    from sdb_ome_sales sales
                    where ".$_where_sql." group by sales.shop_id order by null";
            $sales_info = kernel::database()->select($sql);
            foreach($sales_info as $k=>$info){
                $sales_info[$k]['shop_bn'] = $shopInfos["'".$info['shop_id']."'"]['shop_bn'];
                unset($sales_info[$k]['shop_id']);
            }
            return array('lists' => $sales_info);
        }else{
            return array('lists' => array());
        }
    }
}
