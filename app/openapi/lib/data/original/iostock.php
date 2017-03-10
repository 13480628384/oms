<?php

class openapi_data_original_iostock{

    public function getList($start_time,$end_time,$iostock_bn='',$original_bn='',$branch_bn='',$bn='',$type='',$offset=0,$limit=100){
        if(empty($start_time) || empty($end_time)){
            return false;
        }

        $shopObj = app::get('ome')->model('shop');
        $branchObj = app::get('ome')->model('branch');
        $iostocktypeObj = app::get('ome')->model('iostock_type');
        $memberObj = app::get('ome')->model('members');
        $productsObj = app::get('ome')->model('products');
        $opObj = app::get('desktop')->model('users');
        $countSql = "select count(iostock_id) as _count from sdb_ome_iostock where ";
        $where = "create_time >=".$start_time." and create_time <".$end_time;
        
        if($iostock_bn != ''){
            $where .= " AND iostock_bn = '".$iostock_bn."'";
        }
        if($original_bn != ''){
            $where .= " AND original_bn = '".$original_bn."'";
        }
        if($branch_bn != ''){
            $_branch = $branchObj->getlist('branch_id',array('branch_bn'=>$branch_bn),0,1);
            $where .= " AND branch_id = '".$_branch[0]['branch_id']."'";
        }
        if($bn != ''){
            $where .= " AND bn = '".$bn."'";
        }
        if($type != ''){
            // $_type = $iostocktypeObj->getlist('type_id',array('type_name'=>$type),0,1);
            // $where .= " AND type_id = '".$_type[0]['type_id']."'";
            $where .= " AND type_id = '" . $type . "'";
        }

        $countList = kernel::database()->selectrow($countSql.$where);

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
            $lists = kernel::database()->select($listSql.$where." order by create_time asc limit ".$offset.",".$limit."");
            
            //获取调拨出入库 调拨单号
            $arr_original_ids = array();
            $arr_appropriation_type_ids = array("4","40");
            foreach ($lists as $var_list){
                if(in_array($var_list["type_id"],$arr_appropriation_type_ids)){
                    $arr_original_ids[] = $var_list["original_id"];
                }
            }
            if(!empty($arr_original_ids)){
                $taoguaniostockorder_iso_obj = app::get('taoguaniostockorder')->model('iso');
                $taoguaniostockorder_infos = $taoguaniostockorder_iso_obj->getList("iso_id,appropriation_no",array('iso_id|in'=>$arr_original_ids));
                $rl_original_id_appropriation_no = array();
                foreach ($taoguaniostockorder_infos as $var_taoguaniostockorder_info){
                    $rl_original_id_appropriation_no[$var_taoguaniostockorder_info["iso_id"]] = $var_taoguaniostockorder_info["appropriation_no"];
                }
            }
            
            foreach($lists as &$v){

                $v['branch_bn'] = $branchInfos[$v['branch_id']]['branch_bn'];
                $v['branch_name'] = $branchInfos[$v['branch_id']]['name'];

                $_product = $productsObj->getlist('name,barcode',array('bn'=>$v['bn']),0,1);
                $v['barcode'] = $_product[0]['barcode'];
                $v['name'] = $_product[0]['name'];
                $v['type_name'] = $iostocktypeInfos[$v['type_id']];
                
                if(!empty($rl_original_id_appropriation_no) && in_array($v["type_id"],$arr_appropriation_type_ids)){
                    $v["appropriation_no"] = $rl_original_id_appropriation_no[$v["original_id"]];
                }
                
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