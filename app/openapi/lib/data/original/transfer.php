<?php

class openapi_data_original_transfer{

    public function add($data){
        $result = array('rsp'=>'succ');

        $branch_product_mdl = app::get('ome')->model('branch_product');
        $products_mdl = app::get('ome')->model('products');
        $supplier_mdl = app::get('purchase')->model('supplier');
        $branch_mdl = app::get('ome')->model('branch');

        $_supplier= $supplier_mdl->dump(array('name'=>$data['vendor']), 'supplier_id');
        $_branch = $branch_mdl->getList('branch_id',array('branch_bn'=>$data['branch_bn']));
        if( !$_branch ){
            $result['rsp'] = 'fail';
            $result['msg'] = '仓库不存在';
            return $result;
        }
        
        $type = array('E'=>'70','A'=>'7','G'=>'200','F'=>'100','K'=>'400','J'=>'300','Z'=>'700','Y'=>'800');
        $inType = array('E','G','K','Y');
        $outType = array('A','F','J','Z');
        $sdf['type_id'] = $type[$data['type']];
        $sdf['iso_price'] = $data['delivery_cost'] ? $data['delivery_cost'] : 0;
        $sdf['supplier'] = $data['vendor'];
        $sdf['supplier_id'] = $_supplier['supplier_id'];
        $sdf['branch'] = $_branch[0]['branch_id'];
        $sdf['iostockorder_name'] = $data['name'];
        $sdf['operator'] = $data['operator'];
        $sdf['memo'] = $data['memo'];
        $sdf['confirm'] = $data['confirm'];
        $items = $data['items'];

        if(count($items)<=0 || !$items){
            $result['rsp'] = 'fail';
            $result['msg'] = '缺少出入库商品';
            return $result;
        }
        foreach($items as $v){
            $product = $products_mdl->getlist('product_id,unit',array('bn'=>$v['bn']));
            if (!$product) {
                $result['rsp'] = 'fail';
                $result['msg'] = sprintf('货品[%s]不存在',$v['bn']);
                return $result;
            }

            if($v['nums'] == 0){
                $result['rsp'] = 'fail';
                $result['msg'] = '['.$v['bn'].']库存数量不能为0';
                return $result;
            }
            if(in_array($data['type'],$outType)){
                $aRow = $branch_product_mdl->dump(array('product_id'=>$product[0]['product_id'],'branch_id'=>$sdf['branch']),'store');
                if($v['nums'] > $aRow['store']){
                    $result['rsp'] = 'fail';
                    $result['msg'] = '货号：'.$v['bn'].'出库数不可大于库存数'.$aRow['store'];
                    return $result;
                }
            }

            $products[$product[0]['product_id']] = array(
                'bn'=>$v['bn'],
                'nums'=>$v['nums'],
                'unit'=>$product[0]['unit'],
                'name'=>$v['name'],
                'price'=>$v['price'],
            );

        }
        $sdf['products'] = $products;

        $msg = '';
        $rs = kernel::single('console_iostockorder')->save_iostockorder($sdf,$msg);
        if($rs){
            $result['data'] = kernel::single('console_iostockorder')->getIoStockOrderBn();
        }else{
            $result['rsp'] = 'fail';
            $result['msg'] = $msg;
        }
        
        
        return $result;
    }
    
    public function getList($start_time,$end_time,$original_bn='',$supplier_bn='',$branch_bn='',$t_type='',$offset=0,$limit=100){
        if(empty($start_time) || empty($end_time)){
            return false;
        }
        
        $iostockObj = app::get('ome')->model('iostock');
        $iostocktypeObj = app::get('ome')->model('iostock_type');
        $branchObj = app::get('ome')->model('branch');
        $productsObj = app::get('ome')->model('products');
        
        $countSql = "select count(iostock_id) as _count from sdb_ome_iostock where ";
        
        $where = " create_time >=".$start_time." and create_time <".$end_time;
    
        if($original_bn != ''){
            $where .= " AND original_bn = '".$original_bn."'";
        }
        if($branch_bn != ''){
            $_branch = $branchObj->getlist('branch_id',array('branch_bn'=>$branch_bn),0,1);
            $where .= " AND branch_id = '".$_branch[0]['branch_id']."'";
        }
        if($supplier_bn != ''){
            $supplierObj = app::get('purchase')->model('supplier');
            $_supplier = $supplierObj->getlist('supplier_id',array('bn'=>$supplier_bn),0,1);
            $where .= " AND supplier_id = '".$_supplier[0]['supplier_id']."'";
        }
        if($t_type != ''){
            $ioType = array('E'=>'70','A'=>'7','G'=>'200','F'=>'100','K'=>'400','J'=>'300','Z'=>'700','Y'=>'800');
            $where .= " and type_id=".intval($ioType[$t_type]);
        }else{
            $where .= " and type_id in(70,7,200,100,400,300,700,800)";
        }
        
        $countList = $iostockObj->db->selectrow($countSql.$where);
    
        if(intval($countList['_count']) >0){
    
            $iostocktypeInfos = array();
            $iostocktype_arr = $iostocktypeObj->getList('type_id,type_name', array(), 0, -1);
            foreach ($iostocktype_arr as $k => $iostocktype){
                $iostocktypeInfos[$iostocktype['type_id']] = $iostocktype['type_name'];
            }
    
            $branchInfos = array();
            $branch_arr = $branchObj->getList('branch_id,branch_bn,name', array(), 0, -1);
            foreach ($branch_arr as $k => $branch){
                $branchInfos[$branch['branch_id']] = array('branch_bn'=>$branch['branch_bn'],'name'=>$branch['name']);
            }
    
            $listSql = "select * from sdb_ome_iostock where ";
            $lists = $iostockObj->db->select($listSql.$where." order by create_time asc limit ".$offset.",".$limit."");
    
            //统一获取货品信息
            $product_bns = array();
            foreach($lists as $var_p){
                if(!in_array($var_p["bn"],$product_bns)){
                    $product_bns[] = $var_p["bn"];
                }
            }
            $productInfo = $productsObj->getlist('bn,name,barcode',array('bn|in'=>$product_bns));
            $rl_p_bn_p_info = array();
            foreach ($productInfo as $var_productInfo){
                $temp_p = array(
                    "name" => $var_productInfo["name"],
                    "barcode" => $var_productInfo["barcode"]
                );
                $rl_p_bn_p_info[$var_productInfo["bn"]] = $temp_p;
            }
            
            foreach($lists as &$v){
                $v['branch_bn'] = $branchInfos[$v['branch_id']]['branch_bn'];
                $v['branch_name'] = $branchInfos[$v['branch_id']]['name'];
                $v['barcode'] = $rl_p_bn_p_info[$v["bn"]]['barcode'];
                $v['name'] = $rl_p_bn_p_info[$v["bn"]]['name'];
                $v['type_name'] = $iostocktypeInfos[$v['type_id']];
            }
            
            return array(
                    'lists' => $lists,
                    'count' => $countList['_count'],
            );
            
        }else{
            return array(
                    'lists' => array(),
                    'count' => 0,
            );
        }
    }
    
}