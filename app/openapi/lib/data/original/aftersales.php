<?php

class openapi_data_original_aftersales{

    public function getList($start_time,$end_time,$offset=0,$limit=100){
        if(empty($start_time) || empty($end_time)){
            return false;
        }

        $countList = kernel::database()->selectrow("select count(aftersale_id) as _count from sdb_sales_aftersale where aftersale_time >=".$start_time." and aftersale_time <".$end_time);

        if(intval($countList['_count']) >0){
            $shopObj = app::get('ome')->model('shop');
            $branchObj = app::get('ome')->model('branch');
            $orderObj = app::get('ome')->model('orders');
            $memberObj = app::get('ome')->model('members');
            $returnProductObj = app::get('ome')->model('return_product');
            $reshipObj = app::get('ome')->model('reship');
            $refundApplyObj = app::get('ome')->model('refund_apply');
            $opObj = app::get('desktop')->model('users');
            $productObj = app::get('ome')->model('products');
            $shopInfos = array();
            $shop_arr = $shopObj->getList('shop_id,shop_bn,name', array(), 0, -1);
            foreach ($shop_arr as $k => $shop){
                $shopInfos[$shop['shop_id']] = $shop;
            }

            $branchInfos = array();
            $branch_arr = $branchObj->getList('branch_id,name', array(), 0, -1);
            foreach ($branch_arr as $k => $branch){
                $branchInfos[$branch['branch_id']] = $branch['name'];
            }

            $aftersaleLists = kernel::database()->select("select * from sdb_sales_aftersale where aftersale_time >=".$start_time." and aftersale_time <".$end_time." order by aftersale_time asc limit ".$offset.",".$limit."");

            $aftersaleIds = array();
            $orderIds = array();
            $memberIds = array();
            $returnIds = array();
            $reshipIds = array();
            $refundApplyIds = array();
            $opIds = array();
            foreach ($aftersaleLists as $k => $aftersale){
                $aftersaleIds[] = $aftersale['aftersale_id'];

                if(intval($aftersale['order_id'])>0 && !in_array($aftersale['order_id'],$orderIds)){
                    $orderIds[] =  $aftersale['order_id'];
                }

                if(intval($aftersale['member_id'])>0 && !in_array($aftersale['member_id'],$memberIds)){
                    $memberIds[] = $aftersale['member_id'];
                }

                if(intval($aftersale['return_id'])>0 && !in_array($aftersale['return_id'],$returnIds)){
                    $returnIds[] = $aftersale['return_id'];
                }

                if(intval($aftersale['reship_id'])>0 && !in_array($aftersale['reship_id'],$reshipIds)){
                    $reshipIds[] = $aftersale['reship_id'];
                }

                if(intval($aftersale['return_apply_id'])>0 && !in_array($aftersale['return_apply_id'],$refundApplyIds)){
                    $refundApplyIds[] = $aftersale['return_apply_id'];
                }

                if(intval($aftersale['check_op_id'])>0 && !in_array($aftersale['check_op_id'],$opIds)){
                    $opIds[] = $aftersale['check_op_id'];
                }

                if(intval($aftersale['op_id'])>0 && !in_array($aftersale['op_id'],$opIds)){
                    $opIds[] = $aftersale['op_id'];
                }

                if(intval($aftersale['refund_op_id'])>0 && !in_array($aftersale['refund_op_id'],$opIds)){
                    $opIds[] = $aftersale['refund_op_id'];
                }
            }

            $order_arr = $orderObj->getList('order_id,order_bn',array('order_id'=>$orderIds),0,-1);
            foreach ($order_arr as $k => $order){
                $orderInfos[$order['order_id']] = $order['order_bn'];
            }

            $member_arr = $memberObj->getList('member_id,name',array('member_id'=>$memberIds),0,-1);
            foreach ($member_arr as $k => $member){
                $memberInfos[$member['member_id']] = $member['name'];
            }

            $return_arr = $returnProductObj->getList('return_id,return_bn',array('return_id'=>$returnIds),0,-1);
            foreach ($return_arr as $k => $return){
                $returnInfos[$return['return_id']] = $return['return_bn'];
            }

            $reship_arr = $reshipObj->getList('reship_id,reship_bn',array('reship_id'=>$reshipIds),0,-1);
            foreach ($reship_arr as $k => $reship){
                $reshipInfos[$reship['reship_id']] = $reship['reship_bn'];
            }

            $refundApply_arr = $refundApplyObj->getList('apply_id,refund_apply_bn',array('apply_id'=>$refundApplyIds),0,-1);
            foreach ($refundApply_arr as $k => $refundApply){
                $refundApplyInfos[$refundApply['apply_id']] = $refundApply['refund_apply_bn'];
            }

            $op_arr = $opObj->getList('user_id,name',array('user_id'=>$opIds),0,-1);
            foreach ($op_arr as $k => $op){
                $opInfos[$op['user_id']] = $op['name'];
            }
            $aftersaleInfos = array();
            foreach ($aftersaleLists as $k => $aftersale){
                $aftersaleInfos[$aftersale['aftersale_id']] = $aftersale;
                $aftersaleInfos[$aftersale['aftersale_id']]['order_bn'] = $orderInfos[$aftersale['order_id']];
                $aftersaleInfos[$aftersale['aftersale_id']]['shop_bn'] = $shopInfos[$aftersale['shop_id']]['shop_bn'];
                $aftersaleInfos[$aftersale['aftersale_id']]['shop_name'] = $shopInfos[$aftersale['shop_id']]['name'];
                $aftersaleInfos[$aftersale['aftersale_id']]['member_name'] = $memberInfos[$aftersale['member_id']];
                $aftersaleInfos[$aftersale['aftersale_id']]['return_bn'] = $returnInfos[$aftersale['return_id']];
                $aftersaleInfos[$aftersale['aftersale_id']]['reship_bn'] = $reshipInfos[$aftersale['reship_id']];
                $aftersaleInfos[$aftersale['aftersale_id']]['refund_apply_bn'] = $aftersale['return_apply_bn'];
                $aftersaleInfos[$aftersale['aftersale_id']]['aftersale_type'] = $this->getType($aftersale['return_type']);
                $aftersaleInfos[$aftersale['aftersale_id']]['check_op_name'] = $opInfos[$aftersale['check_op_id']];
                $aftersaleInfos[$aftersale['aftersale_id']]['op_name'] = $opInfos[$aftersale['op_id']];
                $aftersaleInfos[$aftersale['aftersale_id']]['refund_op_name'] = $opInfos[$aftersale['refund_op_id']];

                $aftersaleInfos[$aftersale['aftersale_id']]['aftersale_items'] = array();
            }

            if(count($aftersaleIds) == 1){
                $_where_sql = " aftersale_id =".$aftersaleIds[0]."";
            }else{
                $_where_sql = " aftersale_id in(".implode(',', $aftersaleIds).")";
            }

            $aftersale_items = kernel::database()->select("select * from sdb_sales_aftersale_items where ".$_where_sql."");
            foreach ($aftersale_items as $k =>$aftersale_item){
                
                if($aftersale_item['return_type'] == 'change') continue;
                $product = $productObj->dump(array('product_id'=>$aftersale_item['product_id']),'barcode');
                $aftersale_item['barcode'] = $product['barcode'];
                if(isset($aftersaleInfos[$aftersale_item['aftersale_id']])){
                    $aftersale_item['branch_name'] = $branchInfos[$aftersale_item['branch_id']];
                    $aftersaleInfos[$aftersale_item['aftersale_id']]['aftersale_items'] = array_merge($aftersaleInfos[$aftersale_item['aftersale_id']]['aftersale_items'],array($aftersale_item));
                }
            }

            return array(
                'lists' => $aftersaleInfos,
                'count' => $countList['_count'],
            );
        }else{
            return array(
                'lists' => array(),
                'count' => 0,
            );
        }
    }

    private function getType($key){
        $types = array(
            'return' =>'退货',
			'change' => '换货',
			'refuse' => '拒绝收货',
			'refund' => '退款',
        );
        return $types[$key];
    }

}