<?php

class openapi_data_original_po{

    public function add($data){
        $result = array('rsp'=>'succ');

        $supplier_mdl = app::get('purchase')->model('supplier');
        $branch_mdl = app::get('ome')->model('branch');
        $po_mdl = app::get('purchase')->model('po');
        $formatFilter=kernel::single('openapi_format_abstract');
        $po_type = array('1'=>'cash','2'=>'credit');
        $supplier= $supplier_mdl->dump(array('name'=>$data['vendor']), 'supplier_id');
        $_branch = $branch_mdl->getList('branch_id',array('branch_bn'=>$data['branch_bn']));

        $sdf['supplier_id'] = $supplier['supplier_id'];
        $sdf['operator'] = 'system';
        $sdf['po_type'] = $po_type[$data['type']];
        $sdf['name'] = $formatFilter->charFilter($data['name']);
        $sdf['branch_id'] = $_branch[0]['branch_id'];
        $sdf['arrive_time'] = $data['arrive_time'];
        $sdf['deposit'] = $data['deposit_balance'];
        $sdf['deposit_balance'] = $data['deposit_balance'];
        $sdf['delivery_cost'] = $data['delivery_cost'];
        $sdf['operator'] = $data['operator'];
        $sdf['memo'] = $formatFilter->charFilter($data['memo']);
        $sdf['items'] = $data['items'];

        $rs = $po_mdl->savePo($sdf);
        if($rs['status'] == 'success'){
            $result['data'] = $rs['data'];
        }else{
            $result['rsp'] = 'fail';
            $result['msg'] = $rs['msg'];
        }

        return $result;
    }
    
    public function getList($filter,$offset=0,$limit=100){
    	$po_mdl = app::get('purchase')->model('po');
    	$poItems_mdl = app::get('purchase')->model('po_items');
    	$supplier_mod = app::get('purchase')->model('supplier');
    	$branch_mod = app::get('ome')->model('branch');
        $formatFilter=kernel::single('openapi_format_abstract');
    	if(isset($filter['supplier'])){
            $supplierName = $filter['supplier'];
            $supplier_id = $supplier_mod->getList('supplier_id',array('name'=>$supplierName));
            $supplier_id = $supplier_id[0]['supplier_id'];
            unset($filter['supplier']);
            $filter['supplier_id'] = $supplier_id;
    	}

    	$data = $po_mdl->getList('po_id,name as po_name,po_bn,supplier_id as supplier,purchase_time as po_time,amount,operator,branch_id as branch,
    							po_status,statement as statement_status,check_status,eo_status,delivery_cost as logistic_fee,
    							product_cost as item_cost,deposit,deposit_balance',
    							$filter,($offset-1)*$limit,$limit);
        foreach ($data as $k => $v){
            $v['po_time'] = date('Y-m-d H:i:s',$v['po_time']);
            if($supplierName){
                $supplier_name = $supplierName;
            }else{
                $supplier_name = $supplier_mod->getList('name',array('supplier_id'=>$v['supplier']));
                $supplier_name = $supplier_name[0]['supplier_name'];
            }
            $branch_name = $branch_mod->getList('name',array('branch_id'=>$v['branch']));
            $branch_name = $branch_name[0]['name'];

            $data[$k]['supplier'] = $supplier_name;
            $data[$k]['branch'] = $branch_name;

            $itemInfos = $poItems_mdl->getList('bn as product_bn,name as product_name, price,num, in_num, status', array('po_id'=>$v['po_id']));
            $v['po_bn']= $formatFilter->charFilter($v['po_bn']);
            if(!empty($itemInfos)){
                foreach ($itemInfos as $itemInfo){
                    $itemInfo['product_bn']= $formatFilter->charFilter($itemInfo['product_bn']);
                    $itemInfo['product_name']= $formatFilter->charFilter($itemInfo['product_name']);
                }
                $v['items']= $itemInfos;
                $result[] =$v;
            }
    	}
    	return $result;
    }
    
}