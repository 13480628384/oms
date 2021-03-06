<?php
class ome_order_import {

    function run(&$cursor_id,$params,&$errmsg){
        //danny_freeze_stock_log
        define('FRST_OPER_NAME','system');
        define('FRST_TRIGGER_OBJECT_TYPE','订单导入冻结库存');
        define('FRST_TRIGGER_ACTION_TYPE','ome_order_import：run');
        foreach($params['sdfdata'] as $v){
			$mdl = app::get($params['app'])->model($params['mdl']);
            if(!$mdl->create_order($v)){;
    			$m = $mdl->db->errorinfo();
    			kernel::log("errmsg = ".$m);
    			if(!empty($m)){		
    				$errmsg.=$m.";";
    			}
			}
			#订单保存后，如果是货到付款类型订单，增加应收金额
			if($v['shipping']['is_cod'] == 'true'){
			    $oObj_orextend = app::get($params['app'])->model("order_extend");
			    $code_data = array('order_id'=>$v['order_id'],'receivable'=>$v['total_amount'],'sellermemberid'=>$v['member_id']);
			    $oObj_orextend->save($code_data);
			}
			#订单保存后，如果是货到付款类型订单，增加应收金额
			if($v['shipping']['is_cod'] == 'true'){
			    $oObj_orextend = app::get($params['app'])->model("order_extend");
			    $code_data = array('order_id'=>$v['order_id'],'receivable'=>$v['total_amount'],'sellermemberid'=>$v['member_id']);
			    $oObj_orextend->save($code_data);
			}
        }
        return false;
    }
}